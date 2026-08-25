<?php

namespace Database\Seeders;

use App\Repositories\OptionRepo;
use Illuminate\Database\Seeder;

class VenueRatesSeeder extends Seeder
{
    public function run(OptionRepo $optionRepo): void
    {
        $rates = [
            [
                'venue' => 'Plenary Hall',
                'pax' => 500,
                'outsider' => ['weekday' => 113000, 'weekend' => 120800, 'per_hour' => 2840],
                'internal_core_20' => ['half_day' => 11320, 'per_day' => 22600, 'per_hour' => 7100],
                'internal_ext_50' => ['half_day' => 28300, 'per_day' => 52500],
            ],
            [
                'venue' => 'Multi-Purpose Hall',
                'pax' => 150,
                'outsider' => ['weekday' => 81100, 'weekend' => 88200, 'per_hour' => 2900],
                'internal_core_20' => ['half_day' => 8120, 'per_day' => 16220, 'per_hour' => 7250],
                'internal_ext_50' => ['half_day' => 20300, 'per_day' => 40550],
            ],
            [
                'venue' => 'Plenary and Multi-Purpose Hall Bundle',
                'pax' => 650,
                'outsider' => ['weekday' => 174700, 'weekend' => 258300, 'per_hour' => 5166],
                'internal_core_20' => ['half_day' => 17496, 'per_day' => 34938, 'per_hour' => 12915],
                'internal_ext_50' => ['half_day' => 43740, 'per_day' => 87345],
            ],
            [
                'venue' => 'Plenary and Multi-Purpose Hall Bundle with AVS/AS',
                'pax' => 650,
                'outsider' => ['per_hour' => 5571],
                'internal_core_20' => ['half_day' => 22194, 'per_day' => 44334, 'per_hour' => 13927.5],
                'internal_ext_50' => ['half_day' => 55485, 'per_day' => 110835],
            ],
            [
                'venue' => 'Training Room',
                'pax' => 50,
                'outsider' => ['per_hour' => 840],
                'internal_core_20' => ['half_day' => 2274, 'per_day' => 4570, 'per_hour' => 2100],
                'internal_ext_50' => ['half_day' => 5685, 'per_day' => 11425],
            ],
            [
                'venue' => 'Multi-Purpose Hall and Training Room Bundle',
                'pax' => 200,
                'outsider' => ['per_hour' => 3366],
                'internal_core_20' => ['half_day' => 9354.6, 'per_day' => 18711, 'per_hour' => 8415],
                'internal_ext_50' => ['half_day' => 23386.5, 'per_day' => 46777.5],
            ],
            [
                'venue' => 'Meeting Room',
                'pax' => 15,
                'outsider' => ['status' => 'free'],
                'internal_core_20' => ['status' => 'free'],
                'internal_ext_50' => ['status' => 'free'],
            ],
        ];

        $optionRepo->upsertOption('venue_rates', json_encode($rates), [
            'label' => 'Venue Rental Rates',
            'description' => 'Pricing matrix for venue rentals.',
            'group' => 'rentals',
            'type' => 'json'
        ]);
    }
}
