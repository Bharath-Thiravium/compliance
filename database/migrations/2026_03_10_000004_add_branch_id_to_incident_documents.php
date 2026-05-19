<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incident_documents')) {
            Schema::table('incident_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('incident_documents', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id')->index();
                    $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('incident_documents')) {
            Schema::table('incident_documents', function (Blueprint $table) {
                if (Schema::hasColumn('incident_documents', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};
