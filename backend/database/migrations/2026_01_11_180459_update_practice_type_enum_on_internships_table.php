<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
        ALTER TABLE internships
        MODIFY COLUMN practice_type
        ENUM('standard','paid_employment_contract','paid_invoices')
        NOT NULL DEFAULT 'standard'
    ");
    }

    public function down(): void
    {
        DB::statement("
        ALTER TABLE internships
        MODIFY COLUMN practice_type
        ENUM('standard','paid_employment_contract','paid_invoices')
        NOT NULL DEFAULT 'standard'
    ");
    }
};
