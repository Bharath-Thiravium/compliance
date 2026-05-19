<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bonus_records')) {
            Schema::table('bonus_records', function (Blueprint $table) {
                if (!Schema::hasColumn('bonus_records', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id')->index();
                    $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                }
                if (!Schema::hasColumn('bonus_records', 'status')) {
                    $table->enum('status', ['paid', 'unpaid'])->default('paid')->after('payment_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bonus_records')) {
            Schema::table('bonus_records', function (Blueprint $table) {
                if (Schema::hasColumn('bonus_records', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                if (Schema::hasColumn('bonus_records', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
