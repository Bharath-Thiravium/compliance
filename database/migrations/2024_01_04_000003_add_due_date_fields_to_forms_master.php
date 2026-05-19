<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_forms_master', function (Blueprint $table) {
            $table->integer('due_day')->nullable()->after('frequency');
            $table->integer('due_month')->nullable()->after('due_day');
            $table->integer('grace_days')->nullable()->after('due_month');
        });
    }

    public function down(): void
    {
        Schema::table('compliance_forms_master', function (Blueprint $table) {
            $table->dropColumn(['due_day', 'due_month', 'grace_days']);
        });
    }
};
