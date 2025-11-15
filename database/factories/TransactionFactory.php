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

    protected static int $countID1 = 2;
    protected static int $countID2 = 2;
    protected static int $countID3 = 2;
    protected static int $countID4 = 2;
    protected static int $countID5 = 2;
    protected static int $countID6 = 2;
    protected static array $usedBarcodes = [];

    /**
     * Define the domain's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transac_type = $this->faker->randomElement([Inventory::INCOMING->value, Inventory::OUTGOING->value]);
        $room = $this->faker->randomElement(['01', '02', '03', '04', '05', '06']);

        $latestBarcode = Transaction::where('barcode', 'like', "CBC-$room-%")
            ->orderByDesc('barcode')
            ->value('barcode');

        if ($latestBarcode) {
            $lastNum = (int) substr($latestBarcode, -6);
            $counter = $lastNum + 1;
        } else {
            $counter = 1; // first available after CBC-XX-000001
        }

        do {
            $newBarcode = 'CBC-' . $room . '-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
            $counter++;
        } while (
            Transaction::where('barcode', $newBarcode)->exists()
            || in_array($newBarcode, TransactionFactory::$usedBarcodes, true)
        );

        NewBarcode::updateOrCreate(
            ['room' => $room],
            ['barcode' => $newBarcode]
        );

        TransactionFactory::$usedBarcodes[] = $newBarcode;


        if ($transac_type === Inventory::INCOMING->value)
            return [
                'item_id' => Item::all()->random()->id,
                'barcode' => $newBarcode,
                'transac_type' => Inventory::INCOMING->value,
                'quantity' => $this->faker->randomNumber(3),
                'unit_price' => $this->faker->randomNumber(2),
                'unit' => $this->faker->randomElement(['kg', 'g', 'mg', 'L', 'mL', 'pc']),
                'total_cost' => $this->faker->randomNumber(2),
                'personnel_id' => Personnel::all()->random()->id,
                'user_id' => User::all()->random()->id,
                'expiration' => $this->faker->date(),
                'remarks' => $this->faker->text,
            ];
        $existing = Transaction::all();

        if ($existing->count()) {
            $existing = $existing->random();
            return [
                'item_id' => $existing->item_id,
                'barcode' => $existing->barcode,
                'transac_type' => Inventory::OUTGOING->value,
                'quantity' => $this->faker->randomNumber(3) * -1,
                'unit_price' => $existing->unit_price,
                'unit' => $existing->unit,
                'total_cost' => $existing->total_cost,
                'personnel_id' => Personnel::all()->random()->id,
                'user_id' => User::all()->random()->id,
                'expiration' => $existing->expiration,
                'remarks' => $this->faker->text,
            ];
        }

        return [
                'item_id' => Item::all()->random()->id,
                'barcode' => null,
                'transac_type' => Inventory::OUTGOING->value,
                'quantity' => $this->faker->randomNumber(3) * -1,
                'unit_price' => null,
                'unit' => null,
                'total_cost' => null,
                'personnel_id' => Personnel::all()->random()->id,
                'user_id' => User::all()->random()->id,
                'expiration' => $this->faker->date(),
                'remarks' => $this->faker->text,
            ];

    }
}
