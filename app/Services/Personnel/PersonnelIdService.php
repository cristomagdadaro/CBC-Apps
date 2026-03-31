<?php

namespace App\Services\Personnel;

use App\Models\NewBarcode;
use Illuminate\Support\Facades\DB;

class PersonnelIdService
{
    private const COUNTER_ROOM = 'personnel_external_id';
    private const COUNTER_NAME = 'For OJT/Thesis/Outsider ID';

    public function previewNextExternalEmployeeId(): string
    {
        $counter = NewBarcode::query()
            ->where('room', self::COUNTER_ROOM)
            ->first();

        return $this->nextValueFromCurrent($counter?->barcode);
    }

    public function consumeNextExternalEmployeeId(): string
    {
        return DB::transaction(function () {
            $counter = NewBarcode::query()
                ->lockForUpdate()
                ->where('room', self::COUNTER_ROOM)
                ->first();

            $nextValue = $this->nextValueFromCurrent($counter?->barcode);

            if ($counter) {
                $counter->forceFill([
                    'barcode' => $nextValue,
                    'name' => self::COUNTER_NAME,
                ])->save();
            } else {
                NewBarcode::query()->create([
                    'room' => self::COUNTER_ROOM,
                    'barcode' => $nextValue,
                    'name' => self::COUNTER_NAME,
                ]);
            }

            return $nextValue;
        });
    }

    private function nextValueFromCurrent(?string $currentValue): string
    {
        $currentYear = now()->format('y');

        if (! $currentValue || ! preg_match('/^CBC-(\d{2})-(\d{4})$/', $currentValue, $matches)) {
            return sprintf('CBC-%s-%03d', $currentYear, 1);
        }

        $valueYear = $matches[1];
        $sequence = (int) $matches[2];

        if ($valueYear !== $currentYear) {
            return sprintf('CBC-%s-%03d', $currentYear, 1);
        }

        return sprintf('CBC-%s-%03d', $currentYear, $sequence + 1);
    }
}
