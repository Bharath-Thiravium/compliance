<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workforce_attendance')) {
            if (!Schema::hasIndex('workforce_attendance', ['employee_id', 'attendance_date'])) {
                Schema::table('workforce_attendance', function (Blueprint $table) {
                    $table->index(['employee_id', 'attendance_date'], 'idx_employee_date');
                });
            }
        }

        if (Schema::hasTable('workforce_payroll_entry')) {
            if (!Schema::hasIndex('workforce_payroll_entry', ['tenant_id', 'payroll_cycle_id'])) {
                Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                    $table->index(['tenant_id', 'payroll_cycle_id'], 'idx_tenant_cycle');
                });
            }
        }

        if (Schema::hasTable('contract_labour_deployment')) {
            if (!Schema::hasIndex('contract_labour_deployment', ['tenant_id', 'project_id'])) {
                Schema::table('contract_labour_deployment', function (Blueprint $table) {
                    $table->index(['tenant_id', 'project_id'], 'idx_tenant_project');
                });
            }
        }

        if (Schema::hasTable('contractor_compliance')) {
            if (!Schema::hasIndex('contractor_compliance', ['contractor_id', 'branch_id'])) {
                Schema::table('contractor_compliance', function (Blueprint $table) {
                    $table->index(['contractor_id', 'branch_id'], 'idx_contractor_branch');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_attendance')) {
            if (Schema::hasIndex('workforce_attendance', 'idx_employee_date')) {
                Schema::table('workforce_attendance', function (Blueprint $table) {
                    $table->dropIndex('idx_employee_date');
                });
            }
        }

        if (Schema::hasTable('workforce_payroll_entry')) {
            if (Schema::hasIndex('workforce_payroll_entry', 'idx_tenant_cycle')) {
                Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                    $table->dropIndex('idx_tenant_cycle');
                });
            }
        }

        if (Schema::hasTable('contract_labour_deployment')) {
            if (Schema::hasIndex('contract_labour_deployment', 'idx_tenant_project')) {
                Schema::table('contract_labour_deployment', function (Blueprint $table) {
                    $table->dropIndex('idx_tenant_project');
                });
            }
        }

        if (Schema::hasTable('contractor_compliance')) {
            if (Schema::hasIndex('contractor_compliance', 'idx_contractor_branch')) {
                Schema::table('contractor_compliance', function (Blueprint $table) {
                    $table->dropIndex('idx_contractor_branch');
                });
            }
        }
    }
};
