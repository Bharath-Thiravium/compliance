<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workforce_attendance')) return;

        Schema::table('workforce_attendance', function (Blueprint $table) {
            if (! Schema::hasColumn('workforce_attendance', 'overtime_hours')) {
                $table->decimal('overtime_hours', 8, 2)->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workforce_attendance')) return;

        Schema::table('workforce_attendance', function (Blueprint $table) {
            if (Schema::hasColumn('workforce_attendance', 'overtime_hours')) {
                $table->dropColumn('overtime_hours');
            }
        });
    }
};
