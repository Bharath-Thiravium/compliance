<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('branches', 'address')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->text('address')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'address')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};
