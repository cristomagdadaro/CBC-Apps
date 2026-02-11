<?php

namespace Database\Seeders;

use App\Enums\Inventory;
use App\Models\Category;
use App\Models\Item;
use App\Models\Option;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Repositories\OptionRepo;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Faker\Generator as Faker;
use ParaTest\Options;

class InventoryIcf2026Seeder extends Seeder
{
    use \Illuminate\Foundation\Testing\WithFaker;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = config_path('icf2026.csv');
        $this->faker = \Faker\Factory::create(); // initialize fake
        if (!file_exists($path)) {
            Log::warning('icf2026.csv not found, skipping inventory import.', ['path' => $path]);
            return;
        }

        $supplier = Supplier::firstOrCreate(
            ['name' => 'Unknown'],
            [
                'phone' => '00000000',
                'email' => 'unknown@unknown.com',
                'address' => 'unknown',
                'description' => 'Use this supplier when the supplier is unknown',
            ]
        );

        $handle = fopen($path, 'r');

        if (!$handle) {
            Log::warning('Unable to open icf2026.csv, skipping inventory import.', ['path' => $path]);
            return;
        }

        // Set UTF-8 stream filter to handle special characters
        stream_filter_append($handle, 'convert.iconv.ISO-8859-1/UTF-8');

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return;
        }

        $header = array_map('trim', $header);
        if (end($header) === '') {
            array_pop($header);
        }

        $rowCount = 0;
        $successCount = 0;
        $skipCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;

            if (count($row) === 1 && ($row[0] === null || $row[0] === '')) {
                $skipCount++;
                continue;
            }

            if (count($row) > count($header)) {
                $row = array_slice($row, 0, count($header));
            }

            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), null);
            }

            $data = array_combine($header, $row);
            if (!$data) {
                $skipCount++;
                continue;
            }

            $itemName = trim((string) ($data['item_id'] ?? ''));
            if ($itemName === '') {
                $skipCount++;
                continue;
            }

            $brand = $this->nullableTrim($data['brand'] ?? null);
            $description = $this->nullableTrim($data['description'] ?? null);
            $specifications = $this->nullableTrim($data['specifications'] ?? null);

            $categoryId = $this->nullableTrim($data['category_id'] ?? null);
            $category = $categoryId && is_numeric($categoryId)
                ? Category::find((int) $categoryId)
                : null;
            $fallbackCategory = Category::where('name', 'Office Supplies')->first();

            // Use only name and brand for uniqueness (matches the DB constraint)
            $item = Item::firstOrCreate(
                [
                    'name' => $itemName,
                    'brand' => $brand,
                ],
                [
                    'description' => $description,
                    'specifications' => $specifications,
                    'category_id' => $category?->id ?? $fallbackCategory?->id,
                    'supplier_id' => $supplier->id,
                ]
            );

            // Update fields if they're empty
            $item->fill([
                'description' => $item->description ?: $description,
                'specifications' => $item->specifications ?: $specifications,
                'category_id' => $item->category_id ?: ($category?->id ?? $fallbackCategory?->id),
            ])->save();

            $transacType = strtolower((string) ($data['transac_type'] ?? Inventory::INCOMING->value));
            if (!in_array($transacType, [Inventory::INCOMING->value, Inventory::OUTGOING->value], true)) {
                $transacType = Inventory::INCOMING->value;
            }

            $barcodePrri = $this->nullableTrim($data['barcode_prri'] ?? null);
            $storageOptions = Option::where('label', $row[18])->first();
            $storage = $storageOptions ? $storageOptions->value : null;
            $barcode = TransactionFactory::generateBarcode($storage ?? '00');

            try {
                $transaction = Transaction::factory()->create([
                    'item_id' => $item->id,
                    'barcode' => $barcode,
                    'barcode_prri' => $barcodePrri,
                    'transac_type' => $transacType,
                    'quantity' => $this->toNumeric($data['quantity'] ?? null),
                    'unit' => $this->nullableTrim($data['unit'] ?? null),
                    'unit_price' => $this->toNumeric($data['unit_price'] ?? null),
                    'total_cost' => $this->toNumeric($data['total_cost'] ?? null),
                    'project_code' => $this->nullableTrim($data['project_code'] ?? null),
                    'personnel_id' => $this->resolvePersonnelId($data['personnel_id'] ?? null),
                    'user_id' => $this->resolveUserId($data['user_id'] ?? null),
                    'expiration' => $this->parseDate($data['expiration'] ?? null),
                    'remarks' => $this->nullableTrim($data['remarks'] ?? null),
                    'par_no' => $this->nullableTrim($data['par_no'] ?? null),
                    'condition' => $this->nullableTrim($data['condition'] ?? null),
                ]);

                $createdAt = $this->parseDate($data['created_at'] ?? null);
                if ($createdAt) {
                    $transaction->forceFill([
                        'created_at' => $createdAt,
                    ])->save();
                }

                $successCount++;
            } catch (\Throwable $e) {
                Log::error('Failed to create transaction for item: ' . $itemName, [
                    'error' => $e->getMessage(),
                    'row' => $rowCount,
                ]);
                $skipCount++;
            }
        }

        fclose($handle);

        Log::info('InventoryIcf2026Seeder completed', [
            'total_rows' => $rowCount,
            'successful' => $successCount,
            'skipped' => $skipCount,
        ]);
    }

    private function parseDate(?string $value): ?Carbon
    {
        $value = $this->nullableTrim($value);
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nullableTrim($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function toNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return $normalized !== '' && is_numeric($normalized) ? (float) $normalized : null;
    }

    private function resolvePersonnelId($value): ?int
    {
        if (blank($value)) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', $value));
        $parts = explode(' ', $value);

        $personnel = Personnel::where(function ($q) use ($parts) {
            $q->where('fname', 'LIKE', $parts[0]);
            $q->where('lname', 'LIKE', end($parts));
            if (count($parts) > 2) {
                $q->where(function ($qq) use ($parts) {
                    foreach (array_slice($parts, 1, -1) as $part) {
                        $qq->orWhere('mname', 'LIKE', "%$part%")
                        ->orWhere('suffix', 'LIKE', "%$part%");
                    }
                });
            }
        })->value('id');

        return $personnel ? (int) $personnel : 1;
    }

    private function resolveUserId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
