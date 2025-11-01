<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_activations', function (Blueprint $table) {

            $table->unsignedInteger('company_id')->nullable()->after('id');

            $table->timestamp('expires_at')->nullable()->after('hash');
            $table->timestamp('revoked_at')->nullable()->after('consumed_at');

            $table->timestamp('updated_at')->nullable()->after('created_at');

            $table->index('company_id', 'company_activations_company_id_idx');
            $table->index(['company_id', 'consumed_at', 'revoked_at', 'expires_at'], 'company_activations_active_idx');
        });

        DB::statement("UPDATE company_activations SET expires_at = DATE_ADD(created_at, INTERVAL 48 HOUR) WHERE expires_at IS NULL");

        Schema::table('company_activations', function (Blueprint $table) {
            $table->foreign('company_id', 'company_activations_company_id_fk')
                ->references('id')->on('companies')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('company_activations', function (Blueprint $table) {
            $table->dropForeign('company_activations_company_id_fk');
            $table->dropIndex('company_activations_company_id_idx');
            $table->dropIndex('company_activations_active_idx');

            $table->dropColumn(['company_id', 'expires_at', 'revoked_at', 'updated_at']);
        });
    }
};
