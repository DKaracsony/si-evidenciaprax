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
            ['name' => 'Slovenská republika', 'icon' => '🇸🇰'],
            ['name' => 'Česká republika', 'icon' => '🇨🇿'],
            ['name' => 'Maďarsko', 'icon' => '🇭🇺'],
            ['name' => 'Poľsko', 'icon' => '🇵🇱'],
            ['name' => 'Rakúsko', 'icon' => '🇦🇹'],
            ['name' => 'Ukrajina', 'icon' => '🇺🇦'],
        ];

        foreach ($countries as &$country) {
            $country['created_at'] = Carbon::now();
            $country['updated_at'] = Carbon::now();
        }

        DB::table('countries')->insert($countries);
    }
}
