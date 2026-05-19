<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {

            if (!Schema::hasColumn('branches', 'pf_code')) {
                $table->string('pf_code')->nullable()->after('factory_license_number');
            }

            if (!Schema::hasColumn('branches', 'esi_code')) {
                $table->string('esi_code')->nullable()->after('pf_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['pf_code', 'esi_code']);
        });
    }
};
