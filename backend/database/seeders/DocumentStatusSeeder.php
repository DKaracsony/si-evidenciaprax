<?php

namespace Database\Seeders;

use App\Models\DocumentStatus;
use Illuminate\Database\Seeder;

class DocumentStatusSeeder extends Seeder
{
    public function run(): void
    {
        DocumentStatus::firstOrCreate(['decision' => 'pending'], ['note' => null, 'reviewer_user_id' => null]);
        DocumentStatus::firstOrCreate(['decision' => 'approved'], ['note' => null, 'reviewer_user_id' => null]);
        DocumentStatus::firstOrCreate(['decision' => 'rejected'], ['note' => null, 'reviewer_user_id' => null]);
    }
}
