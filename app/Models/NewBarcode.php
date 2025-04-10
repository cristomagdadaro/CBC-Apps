<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewBarcode extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'room',
    ];

    protected array $searchable  = [
        'barcode',
        'room',
    ];

    public static function GenerateBarcode($room): string
    {
        $roomModel = NewBarcode::where('room', $room)->first();
        $newBarcode = 'CBC-' . str_pad($room, 2, '0', STR_PAD_LEFT) . '-000001';
        if ($roomModel) {

            $lastBarcode = $roomModel->barcode;
            $transac_last_barcode = Transaction::where('barcode', $lastBarcode)->first();

            $numericPart = (int)substr($lastBarcode, 7);
            $numericPart++;
            $newBarcode = 'CBC-' . str_pad($room, 2, '0', STR_PAD_LEFT) . '-' . str_pad($numericPart, 6, '0', STR_PAD_LEFT);

            if ($transac_last_barcode && $lastBarcode === $transac_last_barcode->barcode) {
                $roomModel->update([
                    'barcode' => $newBarcode
                ]);
            } else {
                $newBarcode = $lastBarcode;
            }
        } else {
            NewBarcode::create([
                'barcode' => $newBarcode,
                'room' => $room,
            ]);
        }

        return $newBarcode;
    }
}
