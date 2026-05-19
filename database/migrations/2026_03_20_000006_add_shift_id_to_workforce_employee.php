<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workforce_employee')) {
            Schema::table('workforce_employee', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_employee', 'shift_id')) {
                    $table->string('shift_id')->nullable()->after('designation');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_employee')) {
            Schema::table('workforce_employee', function (Blueprint $table) {
                if (Schema::hasColumn('workforce_employee', 'shift_id')) {
                    $table->dropColumn('shift_id');
                }
            });
        }
    }
};
