<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Test',
                'last_name' => 'Garant',
                'email' => 'test.garant@ukf.sk',
                'password_hash' => bcrypt('password'),
                'role_id' => 1,
                'active' => true,
                'phone_number' => '0905123456'
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Student',
                'email' => 'test.student@student.ukf.sk',
                'password_hash' => bcrypt('password'),
                'role_id' => 2,
                'active' => true,
                'phone_number' => '0905987654'
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'Firma',
                'email' => 'test.firma@domain.com',
                'password_hash' => bcrypt('password'),
                'role_id' => 3,
                'active' => true,
                'phone_number' => '0905345678'
            ]
        ];

        User::insert($users);

        //TODO: next add student_profiles + company_profiles seeders
    }
}
