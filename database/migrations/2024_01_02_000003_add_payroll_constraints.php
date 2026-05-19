<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rename payroll_cycles to workforce_payroll_cycle if needed
        if (Schema::hasTable('payroll_cycles') && !Schema::hasTable('workforce_payroll_cycle')) {
            Schema::rename('payroll_cycles', 'workforce_payroll_cycle');
        }

        // Rename payroll_entries to workforce_payroll_entry if needed
        if (Schema::hasTable('payroll_entries') && !Schema::hasTable('workforce_payroll_entry')) {
            Schema::rename('payroll_entries', 'workforce_payroll_entry');
        }

        // Add unique constraint on workforce_payroll_cycle
        if (Schema::hasTable('workforce_payroll_cycle')) {
            if (!Schema::hasIndex('workforce_payroll_cycle', 'unique_tenant_cycle')) {
                Schema::table('workforce_payroll_cycle', function (Blueprint $table) {
                    $table->unique(['tenant_id', 'cycle_name'], 'unique_tenant_cycle');
                });
            }
        }

        // Add constraints and indexes on workforce_payroll_entry
        if (Schema::hasTable('workforce_payroll_entry')) {
            if (!Schema::hasIndex('workforce_payroll_entry', 'unique_cycle_employee')) {
                Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                    $table->unique(['payroll_cycle_id', 'employee_id'], 'unique_cycle_employee');
                });
            }
            
            if (!Schema::hasColumn('workforce_payroll_entry', 'tenant_id')) {
                Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->after('id')->index();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_payroll_entry')) {
            // Drop foreign keys first using raw SQL
            try {
                DB::statement('ALTER TABLE workforce_payroll_entry DROP FOREIGN KEY workforce_payroll_entry_payroll_cycle_id_foreign');
            } catch (\Exception $e) {}
            
            try {
                DB::statement('ALTER TABLE workforce_payroll_entry DROP FOREIGN KEY workforce_payroll_entry_employee_id_foreign');
            } catch (\Exception $e) {}
            
            // Then drop unique constraints using raw SQL
            try {
                DB::statement('ALTER TABLE workforce_payroll_entry DROP INDEX unique_cycle_employee');
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('workforce_payroll_cycle')) {
            try {
                DB::statement('ALTER TABLE workforce_payroll_cycle DROP INDEX unique_tenant_cycle');
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('workforce_payroll_entry')) {
            Schema::rename('workforce_payroll_entry', 'payroll_entries');
        }

        if (Schema::hasTable('workforce_payroll_cycle')) {
            Schema::rename('workforce_payroll_cycle', 'payroll_cycles');
        }
    }
};
