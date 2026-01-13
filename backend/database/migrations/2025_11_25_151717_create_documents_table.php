<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->text('file_name');

            $table->enum('type', ['agreement', 'statement']);

            $table->foreignId('internship_id')
                ->constrained('internships')
                ->onDelete('cascade');

            $table->foreignId('uploaded_by_user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('document_status_id')
                ->constrained('document_statuses')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
