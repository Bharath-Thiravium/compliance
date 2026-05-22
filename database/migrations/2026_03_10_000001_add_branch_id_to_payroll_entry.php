<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workforce_payroll_entry')) {
            if (!Schema::hasColumn('workforce_payroll_entry', 'branch_id')) {
                Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id')->index();
                });
                
                // Add foreign key using raw SQL
                try {
                    DB::statement('ALTER TABLE workforce_payroll_entry ADD CONSTRAINT workforce_payroll_entry_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL');
                } catch (\Exception $e) {
                    // Foreign key might already exist
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_payroll_entry')) {
            // Drop all foreign keys on branch_id
            try {
                DB::statement('ALTER TABLE workforce_payroll_entry DROP FOREIGN KEY workforce_payroll_entry_branch_id_foreign');
            } catch (\Exception $e) {
                // Try alternative name
            }
            
            try {
                DB::statement('ALTER TABLE workforce_payroll_entry DROP FOREIGN KEY payroll_entries_branch_id_foreign');
            } catch (\Exception $e) {
                // Foreign key doesn't exist
            }
            
            if (Schema::hasColumn('workforce_payroll_entry', 'branch_id')) {
                Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                    $table->dropColumn('branch_id');
                });
            }
        }
    }
};
