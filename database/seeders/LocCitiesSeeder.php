<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = config('loc_cities', []);

        if (!is_array($rows) || empty($rows)) {
            return;
        }

        $payload = [];

        foreach ($rows as $row) {
            $payload[] = [
                'id' => $row['id'],
                'city' => $row['cityDesc'],
                'province' => $row['provDesc'],
                'region' => $row['regDesc'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
            ];
        }

        foreach (array_chunk($payload, 1000) as $chunk) {
            DB::table('loc_cities')->upsert(
                $chunk,
                ['id'],
                ['city', 'province', 'region', 'latitude', 'longitude']
            );
        }
    }
}
