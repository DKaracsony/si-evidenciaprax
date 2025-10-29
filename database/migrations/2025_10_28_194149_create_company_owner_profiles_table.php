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
        Schema::create('company_owner_profiles', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('company_activation_id')->nullable();
            $table->unsignedBigInteger('company_user_id');
            $table->string('role_at_company', 200)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('company_activation_id')->references('id')->on('company_activations')->onDelete('set null');
            $table->foreign('company_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_owner_profiles');
    }
};
