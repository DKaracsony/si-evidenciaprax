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
            // Zoznam praxí študenta – filter podľa študenta + zoradenie podľa created_at
            $table->index(
                ['student_profile_id', 'created_at'],
                'internships_student_profile_created_idx'
            );

            // Praxe firmy – filter podľa company_id
            $table->index(
                'company_id',
                'internships_company_id_idx'
            );

            // Filtrovanie podľa akademického roka
            $table->index(
                'academic_year_id',
                'internships_academic_year_id_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropIndex('internships_student_profile_created_idx');
            $table->dropIndex('internships_company_id_idx');
            $table->dropIndex('internships_academic_year_id_idx');
        });
    }
};
