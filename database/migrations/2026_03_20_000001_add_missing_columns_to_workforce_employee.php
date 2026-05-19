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
                if (!Schema::hasColumn('workforce_employee', 'father_name')) {
                    $table->string('father_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('workforce_employee', 'gender')) {
                    $table->enum('gender', ['M', 'F', 'O'])->nullable()->after('father_name');
                }
                if (!Schema::hasColumn('workforce_employee', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable()->after('gender');
                }
                if (!Schema::hasColumn('workforce_employee', 'permanent_address')) {
                    $table->text('permanent_address')->nullable()->after('date_of_birth');
                }
                if (!Schema::hasColumn('workforce_employee', 'local_address')) {
                    $table->text('local_address')->nullable()->after('permanent_address');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_employee')) {
            Schema::table('workforce_employee', function (Blueprint $table) {
                if (Schema::hasColumn('workforce_employee', 'father_name')) {
                    $table->dropColumn('father_name');
                }
                if (Schema::hasColumn('workforce_employee', 'gender')) {
                    $table->dropColumn('gender');
                }
                if (Schema::hasColumn('workforce_employee', 'date_of_birth')) {
                    $table->dropColumn('date_of_birth');
                }
                if (Schema::hasColumn('workforce_employee', 'permanent_address')) {
                    $table->dropColumn('permanent_address');
                }
                if (Schema::hasColumn('workforce_employee', 'local_address')) {
                    $table->dropColumn('local_address');
                }
            });
        }
    }
};
