<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add compliance_period to workforce_payroll_cycle if not exists
        if (Schema::hasTable('workforce_payroll_cycle')) {
            Schema::table('workforce_payroll_cycle', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_payroll_cycle', 'compliance_period')) {
                    $table->string('compliance_period')->nullable()->comment('YYYY-MM format')->after('tenant_id');
                    $table->index('compliance_period');
                }
            });
        }

        // Add month/year to workforce_attendance if not exists
        if (Schema::hasTable('workforce_attendance')) {
            Schema::table('workforce_attendance', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_attendance', 'compliance_period')) {
                    $table->string('compliance_period')->nullable()->comment('YYYY-MM format')->after('branch_id');
                    $table->index(['tenant_id', 'branch_id', 'compliance_period']);
                }
            });
        }

        // Add to bonus_records if exists
        if (Schema::hasTable('bonus_records')) {
            Schema::table('bonus_records', function (Blueprint $table) {
                if (!Schema::hasColumn('bonus_records', 'compliance_period')) {
                    $table->string('compliance_period')->nullable()->comment('YYYY-MM format')->after('branch_id');
                    $table->index(['tenant_id', 'branch_id', 'compliance_period']);
                }
            });
        }
    }

    public function down(): void
    {
        // Reverse changes
    }
};
