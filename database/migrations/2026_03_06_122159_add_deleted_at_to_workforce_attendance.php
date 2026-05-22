<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToWorkforceAttendance extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('workforce_attendance', 'deleted_at')) {
            Schema::table('workforce_attendance', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('workforce_attendance', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
