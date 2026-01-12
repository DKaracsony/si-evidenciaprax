<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE documents
            MODIFY COLUMN type
            ENUM('agreement','statement','salary_statement','invoice')
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE documents
            MODIFY COLUMN type
            ENUM('agreement','statement','salary_statement','invoice')
            NOT NULL
        ");
    }
};
