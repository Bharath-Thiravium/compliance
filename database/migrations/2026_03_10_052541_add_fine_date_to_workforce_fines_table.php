<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFineDateToWorkforceFinesTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workforce_fines')) {
            if (!Schema::hasColumn('workforce_fines', 'fine_date')) {
                Schema::table('workforce_fines', function (Blueprint $table) {
                    $table->date('fine_date')->nullable()->after('employee_id');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_fines')) {
            if (Schema::hasColumn('workforce_fines', 'fine_date')) {
                Schema::table('workforce_fines', function (Blueprint $table) {
                    $table->dropColumn('fine_date');
                });
            }
        }
    }
}
