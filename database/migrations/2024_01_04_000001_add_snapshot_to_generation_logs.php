<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_generation_logs', function (Blueprint $table) {
            $table->json('generated_snapshot')->nullable()->after('checksum_hash');
        });
    }

    public function down(): void
    {
        Schema::table('compliance_generation_logs', function (Blueprint $table) {
            $table->dropColumn('generated_snapshot');
        });
    }
};
