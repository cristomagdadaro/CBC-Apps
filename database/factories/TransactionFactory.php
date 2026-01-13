<?php

namespace Database\Factories;

use App\Enums\Inventory;
use App\Models\Item;
use App\Models\NewBarcode;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected static array $usedBarcodes = [];

    public function definition(): array
    {
        $transac_type = $this->faker->randomElement([Inventory::INCOMING->value, Inventory::OUTGOING->value]);
        $room = $this->faker->randomElement(['01', '02', '03', '04', '05', '06']);

        if ($transac_type === Inventory::INCOMING->value) {
            $newBarcode = self::generateBarcode($room);

            return [
                'item_id' => Item::all()->random()->id,
                'barcode' => $newBarcode,
                'transac_type' => Inventory::INCOMING->value,
                'quantity' => $this->faker->numberBetween(1, 1000),
                'unit_price' => $this->faker->randomNumber(2),
                'unit' => $this->faker->randomElement(['kg', 'g', 'mg', 'L', 'mL', 'pc']),
                'total_cost' => $this->faker->randomNumber(2),
                'personnel_id' => Personnel::all()->random()->id,
                'user_id' => User::all()->random()->id,
                'expiration' => $this->faker->date(),
                'remarks' => $this->faker->text,
            ];
        }

        $incomingTransaction = Transaction::where('transac_type', Inventory::INCOMING->value)
            ->inRandomOrder()
            ->first();

        if (!$incomingTransaction) {
            return [
                'item_id' => Item::all()->random()->id,
                'barcode' => null,
                'transac_type' => Inventory::OUTGOING->value,
                'quantity' => $this->faker->numberBetween(-1000, -1),
                'unit_price' => null,
                'unit' => $this->faker->randomElement(['kg', 'g', 'mg', 'L', 'mL', 'pc']),
                'total_cost' => null,
                'personnel_id' => Personnel::all()->random()->id,
                'user_id' => User::all()->random()->id,
                'expiration' => null,
                'remarks' => 'Factory Fallback: No INCOMING transactions found.',
            ];
        }

        $availableQuantity = Transaction::where('item_id', $incomingTransaction->item_id)
            ->sum('quantity');

        if ($availableQuantity <= 0) {
            $safeQuantity = 0;
            $remarks = 'Factory: Attempted OUTGOING but stock was 0 or negative.';
        } else {
            $safeQuantity = $this->faker->numberBetween(1, $availableQuantity);
            $remarks = $this->faker->text;
        }

        return [
            'item_id' => $incomingTransaction->item_id,
            'barcode' => $incomingTransaction->barcode,
            'transac_type' => Inventory::OUTGOING->value,
            'quantity' => $safeQuantity * -1,
            'unit_price' => $incomingTransaction->unit_price,
            'unit' => $incomingTransaction->unit,
            'total_cost' => $incomingTransaction->total_cost ? ($incomingTransaction->total_cost / abs($incomingTransaction->quantity)) * $safeQuantity * -1 : null,
            'personnel_id' => Personnel::all()->random()->id,
            'user_id' => User::all()->random()->id,
            'expiration' => $incomingTransaction->expiration,
            'remarks' => $remarks,
            'project_code' => $this->faker->bothify('PC-#####'),
        ];
    }

    public static function generateBarcode(string $room): string
    {
        // Get the last known barcode from the NewBarcode table
        $lastBarcodeRecord = NewBarcode::where('room', $room)->first();
        $latestBarcode = $lastBarcodeRecord?->barcode;

        // Determine starting counter
        if ($latestBarcode) {
            // Extract the numerical part and increment
            $lastNum = (int) substr($latestBarcode, -6);
            $counter = $lastNum + 1;
        } else {
            $counter = 1;
        }

        // Loop until a unique barcode is found
        do {
            $newBarcode = 'CBC-' . $room . '-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
            $counter++;
        } while (
            Transaction::where('barcode', $newBarcode)->exists()
            || in_array($newBarcode, TransactionFactory::$usedBarcodes, true)
        );

        // Save/update the last generated barcode reference
        NewBarcode::updateOrCreate(
            ['room' => $room],
            ['barcode' => $newBarcode]
        );

        // Track used barcodes in memory (factory-only behavior)
        TransactionFactory::$usedBarcodes[] = $newBarcode;

        return $newBarcode;
    }
}
