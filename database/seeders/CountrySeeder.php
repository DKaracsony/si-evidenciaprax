<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Slovenská republika', 'origin' => 'SK'],
            ['name' => 'Česká republika', 'origin' => 'CZ'],
            ['name' => 'Maďarsko', 'origin' => 'HU'],
            ['name' => 'Poľsko', 'origin' => 'PL'],
            ['name' => 'Rakúsko', 'origin' => 'AT'],
            ['name' => 'Ukrajina', 'origin' => 'UA'],
        ];

        foreach ($countries as &$country) {
            $country['created_at'] = Carbon::now();
            $country['updated_at'] = Carbon::now();
        }

        DB::table('countries')->insert($countries);
    }
}
