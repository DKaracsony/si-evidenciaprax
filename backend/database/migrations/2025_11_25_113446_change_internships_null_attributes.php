<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
            $table->date('date_to')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->foreignId('company_id')->nullable()->change();
            $table->foreignId('academic_year_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->date('date_to')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->foreignId('company_id')->nullable(false)->change();
            $table->foreignId('academic_year_id')->nullable(false)->change();
        });
    }
};
