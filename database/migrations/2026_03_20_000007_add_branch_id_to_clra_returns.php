<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clra_returns')) {
            Schema::table('clra_returns', function (Blueprint $table) {
                if (!Schema::hasColumn('clra_returns', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clra_returns')) {
            Schema::table('clra_returns', function (Blueprint $table) {
                if (Schema::hasColumn('clra_returns', 'branch_id')) {
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};
