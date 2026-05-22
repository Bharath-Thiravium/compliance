<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workforce_employee')) return;

        Schema::table('workforce_employee', function (Blueprint $table) {
            $cols = [
                'mobile'         => fn() => $table->string('mobile', 20)->nullable()->after('local_address'),
                'email'          => fn() => $table->string('email')->nullable()->after('mobile'),
                'marital_status' => fn() => $table->string('marital_status', 20)->nullable()->after('email'),
                'nationality'    => fn() => $table->string('nationality', 50)->nullable()->after('marital_status'),
                'skill_type'     => fn() => $table->string('skill_type', 50)->nullable()->after('nationality'),
                'pan'            => fn() => $table->string('pan', 20)->nullable()->after('skill_type'),
                'aadhaar'        => fn() => $table->string('aadhaar', 20)->nullable()->after('pan'),
                'uan_number'     => fn() => $table->string('uan_number', 30)->nullable()->after('aadhaar'),
                'bank_account'   => fn() => $table->string('bank_account', 30)->nullable()->after('uan_number'),
                'bank_name'      => fn() => $table->string('bank_name', 100)->nullable()->after('bank_account'),
                'ifsc'           => fn() => $table->string('ifsc', 20)->nullable()->after('bank_name'),
                'date_of_exit'   => fn() => $table->date('date_of_exit')->nullable()->after('date_of_joining'),
            ];

            foreach ($cols as $col => $definition) {
                if (! Schema::hasColumn('workforce_employee', $col)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workforce_employee')) return;

        $cols = ['mobile', 'email', 'marital_status', 'nationality', 'skill_type',
                 'pan', 'aadhaar', 'uan_number', 'bank_account', 'bank_name', 'ifsc', 'date_of_exit'];

        Schema::table('workforce_employee', function (Blueprint $table) use ($cols) {
            foreach ($cols as $col) {
                if (Schema::hasColumn('workforce_employee', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
