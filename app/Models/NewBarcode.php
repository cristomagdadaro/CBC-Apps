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
        'name',
    ];

    protected array $searchable  = [
        'barcode',
        'room',
        'name',
    ];

    public static function GenerateBarcode($room): string
    {
        $newBarcode = '';
        $roomName = Option::query()
            ->where('value', (string) $room)
            ->whereIn('group', ['storage_locations', 'event_halls', 'laboratories', 'offices', 'screenhouses'])
            ->value('label');

        DB::transaction(function () use ($room, $roomName, &$newBarcode) {
            $roomModel = NewBarcode::lockForUpdate()->where('room', $room)->first();

            if ($roomModel) {
                $lastBarcode = $roomModel->barcode;

                // extract numeric part safely
                preg_match('/CBC-\d{2}-(\d+)/', $lastBarcode, $matches);
                $numericPart = isset($matches[1]) ? (int)$matches[1] : 0;

                // keep previously issued barcodes reserved even after soft delete
                $isUsed = Transaction::withTrashed()
                    ->where('barcode', $lastBarcode)
                    ->exists();

                if ($isUsed) {
                    $numericPart++;
                    $newBarcode = 'CBC-' . str_pad($room, 2, '0', STR_PAD_LEFT) . '-' . str_pad($numericPart, 6, '0', STR_PAD_LEFT);
                    $roomModel->update(['barcode' => $newBarcode, 'name' => $roomName]);
                } else {
                    // reuse last unused barcode
                    $newBarcode = $lastBarcode;
                    if ($roomModel->name !== $roomName) {
                        $roomModel->update(['name' => $roomName]);
                    }
                }
            } else {
                $newBarcode = 'CBC-' . str_pad($room, 2, '0', STR_PAD_LEFT) . '-000001';
                $roomModel = NewBarcode::create([
                    'barcode' => $newBarcode,
                    'room' => $room,
                    'name' => $roomName,
                ]);
            }
        });

        return $newBarcode;

    }
}
