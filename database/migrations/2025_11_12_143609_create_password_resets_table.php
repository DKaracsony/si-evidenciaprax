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
        Schema::create('password_resets', function (Blueprint $t) {
            $t->id();
            $t->dateTime('expires_at');
            $t->timestamp('created_at')->useCurrent();
            $t->string('hash', 255);
            $t->timestamp('consumed_at')->nullable();
            $t->unsignedBigInteger('user_id');
            $t->string('sent_to_mail', 200);

            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};
