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
        $room = $this->faker->randomElement(['01', '02', '03', '04', '05', '06']);

        $roomExist = NewBarcode::where('room', $room)->first();
        //$transac_type = $this->faker->randomElement(config('system_variables.transaction_type'));
        $transac_type =Inventory::INCOMING->value;
        if($roomExist == null){
            $newBarcode = 'CBC-'. $room .'-000001';
            NewBarcode::create([
                'barcode' => $newBarcode,
                'room' => $room,
            ]);
        } else{
            $counter = 0;
            do{
                switch ($room)
                {
                    case '01':
                        $counter = TransactionFactory::$countID1++;
                        break;
                    case '02':
                        $counter = TransactionFactory::$countID2++;
                        break;
                    case '03':
                        $counter = TransactionFactory::$countID3++;
                        break;
                    case '04':
                        $counter = TransactionFactory::$countID4++;
                        break;
                    case '05':
                        $counter = TransactionFactory::$countID5++;
                        break;
                    case '06':
                        $counter = TransactionFactory::$countID6++;
                        break;
                }
                $newBarcode = 'CBC-'. $room .'-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
            }
            while (Transaction::where('barcode', $newBarcode)->exists() || in_array($newBarcode, TransactionFactory::$usedBarcodes, true));
            $roomExist->update([
                'barcode' => $newBarcode
            ]);
        }


        if ($transac_type === Inventory::INCOMING->value)
            return [
                'id' => $this->faker->uuid,
                'item_id' => Item::all()->random()->id,
                'barcode' => $newBarcode,
                'transac_type' => Inventory::INCOMING->value,
                'quantity' => $this->faker->randomNumber(3),
                'unit_price' => $this->faker->randomNumber(2),
                'unit' => $this->faker->randomElement(['kg', 'g', 'mg', 'L', 'mL', 'pc']),
                'total_cost' => $this->faker->randomNumber(2),
                'personnel_id' => null,
                'project_code' => $newBarcode,
                'user_id' => User::all()->random()->id,
                'expiration' => $this->faker->date(),
                'remarks' => $this->faker->text,
            ];

        return [
                'id' => $this->faker->uuid,
                'item_id' => Item::all()->random()->id,
                'barcode' => null,
                'transac_type' => Inventory::OUTGOING->value,
                'quantity' => $this->faker->randomNumber(3) * -1,
                'unit_price' => null,
                'unit' => null,
                'total_cost' => null,
                'personnel_id' => Personnel::all()->random()->id,
                'project_code' => null,
                'user_id' => User::all()->random()->id,
                'expiration' => $this->faker->date(),
                'remarks' => $this->faker->text,
            ];

    }
}
