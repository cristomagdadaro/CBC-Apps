<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($room, &$newBarcode) {
            $roomModel = NewBarcode::lockForUpdate()->where('room', $room)->first();

            if ($roomModel) {
                $lastBarcode = $roomModel->barcode;

                // extract numeric part safely
                preg_match('/CBC-\d{2}-(\d+)/', $lastBarcode, $matches);
                $numericPart = isset($matches[1]) ? (int)$matches[1] : 0;

                // check if last barcode was already used in transactions
                $isUsed = Transaction::where('barcode', $lastBarcode)->exists();

                if ($isUsed) {
                    $numericPart++;
                    $newBarcode = 'CBC-' . str_pad($room, 2, '0', STR_PAD_LEFT) . '-' . str_pad($numericPart, 6, '0', STR_PAD_LEFT);
                    $roomModel->update(['barcode' => $newBarcode]);
                } else {
                    // reuse last unused barcode
                    $newBarcode = $lastBarcode;
                }
            } else {
                $newBarcode = 'CBC-' . str_pad($room, 2, '0', STR_PAD_LEFT) . '-000001';
                $roomModel = NewBarcode::create([
                    'barcode' => $newBarcode,
                    'room' => $room,
                ]);
            }
        });

        return $newBarcode;

    }
}
