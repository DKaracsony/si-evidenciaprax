<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class InternshipStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
          ['name' => 'Vytvorená', 'order_index' => 1, 'created_at' => now()],
          ['name' => 'Potvrdená', 'order_index' => 2, 'created_at' => now()],
          ['name' => 'Zamietnutá', 'order_index' => 2, 'created_at' => now()],
          ['name' => 'Schválená', 'order_index' => 3, 'created_at' => now()],
          ['name' => 'Obhájená', 'order_index' => 4, 'created_at' => now()],
          ['name' => 'Neobhájená', 'order_index' => 4, 'created_at' => now()],
        ];

        Status::insert($statuses);
    }
}
