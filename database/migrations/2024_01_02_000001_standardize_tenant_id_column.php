<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'workforce_employee',
            'workforce_payroll_cycle',
            'workforce_attendance',
            'contractor_master',
            'contract_labour_deployment',
            'clra_returns',
            'incident_documents',
            'inspection_documents',
            'compliance_forms',
            'bonus_records',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'athens_tenant_id')) {
                if (!Schema::hasColumn($table, 'tenant_id')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                    });

                    DB::table($table)->update(['tenant_id' => DB::raw('athens_tenant_id')]);

                    Schema::table($table, function (Blueprint $table) {
                        $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
                        $table->index('tenant_id');
                        $table->dropColumn('athens_tenant_id');
                    });
                }
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'workforce_employee',
            'workforce_payroll_cycle',
            'workforce_attendance',
            'contractor_master',
            'contract_labour_deployment',
            'clra_returns',
            'incident_documents',
            'inspection_documents',
            'compliance_forms',
            'bonus_records',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                if (!Schema::hasColumn($table, 'athens_tenant_id')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->integer('athens_tenant_id')->nullable();
                    });

                    DB::table($table)->update(['athens_tenant_id' => DB::raw('tenant_id')]);
                }

                // Drop all foreign keys on tenant_id using raw SQL
                $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = '{$table}' AND COLUMN_NAME = 'tenant_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
                foreach ($constraints as $constraint) {
                    try {
                        DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
                    } catch (\Exception $e) {}
                }

                // Then drop index
                if (Schema::hasIndex($table, 'tenant_id')) {
                    try {
                        Schema::table($table, function (Blueprint $table) {
                            $table->dropIndex(['tenant_id']);
                        });
                    } catch (\Exception $e) {}
                }

                // Finally drop column
                if (Schema::hasColumn($table, 'tenant_id')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->dropColumn('tenant_id');
                    });
                }
            }
        }
    }
};
