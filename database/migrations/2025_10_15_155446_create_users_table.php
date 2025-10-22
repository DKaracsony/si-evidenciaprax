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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('title_before', 20)->nullable();
            $table->string('title_after', 20)->nullable();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('phone_number', 20);
            $table->foreignId('role_id')->constrained('roles');
            $table->boolean('active')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
