<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //prev --> $table->enum('type', ['agreement', 'statement']);
        DB::statement("
            ALTER TABLE documents
            MODIFY COLUMN type
            ENUM('agreement','statement','salary_statement')
            NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE documents
            MODIFY COLUMN type
            ENUM('agreement','statement')
            NOT NULL
        ");
    }
};
