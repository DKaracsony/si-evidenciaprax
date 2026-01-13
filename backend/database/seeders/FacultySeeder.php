<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('faculties')->insert([
            ['name' => 'Aplikovaná informatika', 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Umelá inteligencia - spracovanie prirodzených jazykov', 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Učiteľstvo informatiky v kombinácii', 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Učiteľstvo matematiky v kombinácii', 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Učiteľstvo vzdelávacej oblasti Matematika a informatika', 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
