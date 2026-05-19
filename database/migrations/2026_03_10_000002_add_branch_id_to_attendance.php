<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workforce_attendance')) {
            Schema::table('workforce_attendance', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_attendance', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id')->index();
                    $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                }
                if (!Schema::hasColumn('workforce_attendance', 'remarks')) {
                    $table->text('remarks')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_attendance')) {
            Schema::table('workforce_attendance', function (Blueprint $table) {
                if (Schema::hasColumn('workforce_attendance', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                if (Schema::hasColumn('workforce_attendance', 'remarks')) {
                    $table->dropColumn('remarks');
                }
            });
        }
    }
};
