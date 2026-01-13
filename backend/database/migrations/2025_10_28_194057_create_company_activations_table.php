<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_activations', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('hash', 64);
            $table->string('sent_to_mail', 200);

            $table->unique('hash', 'company_activations_hash_unique');
            $table->index('sent_to_mail', 'company_activations_mail_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_activations');
    }
};
