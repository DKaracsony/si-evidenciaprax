<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'garant', 'created_at' => now()],
            ['name' => 'študent', 'created_at' => now()],
            ['name' => 'firma', 'created_at' => now()],
            ['name' => 'externý systém', 'created_at' => now()],
        ];

        Role::insert($roles);
    }
}
