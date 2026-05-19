<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWorkforceFinesTableSchema extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workforce_fines')) {
            Schema::table('workforce_fines', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_fines', 'fine_date')) {
                    $table->date('fine_date')->nullable()->after('employee_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_fines')) {
            Schema::table('workforce_fines', function (Blueprint $table) {
                if (Schema::hasColumn('workforce_fines', 'fine_date')) {
                    $table->dropColumn('fine_date');
                }
                if (Schema::hasColumn('workforce_fines', 'reason')) {
                    $table->dropColumn('reason');
                }
                if (Schema::hasColumn('workforce_fines', 'amount')) {
                    $table->dropColumn('amount');
                }
                if (Schema::hasColumn('workforce_fines', 'remarks')) {
                    $table->dropColumn('remarks');
                }
            });
        }
    }
}
