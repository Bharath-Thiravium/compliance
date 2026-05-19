<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compliance_manual_master', function (Blueprint $table) {
            $table->boolean('is_automatable')->default(false)->after('is_event_based');
        });

        // Mark all manual entries that have a direct automated equivalent
        DB::table('compliance_manual_master')
            ->whereIn('id', [
                1,  // Form B - Register of Adult Workers              → FormB
                2,  // Form 12 - Muster Roll cum Wage Register         → Form12
                3,  // Form A - Register of Fines                      → FormA
                4,  // Form C - Register of Deductions                 → FormC
                5,  // Form D - Register of Advances                   → FormD
                9,  // Form 10 - Leave with Wages Register             → Form10
                10, // Form 17 - Register of Overtime                  → Form17
                14, // Form XIII - Register of Workmen (CLRA)          → FormXIII
                15, // Form XIV - Employment Card (CLRA)               → FormXIV
                16, // Form XVI - Muster Roll (CLRA)                   → FormXVI
                17, // Form XIX - Wage Slip (CLRA)                     → FormXIX
                19, // Form 18 - Notice of Dangerous Occurrence        → Form18
                20, // Form 26 - Register of Exemptions                → Form26
                21, // Register of Wages                               → FormXVII
                22, // Register of Attendance                          → FormB
                23, // Register of Employment (Contract Labour)        → FormXIII
                24, // Register of Contractors                         → FormXII
                29, // Register of Overtime                            → FormXXIII
                35, // Register of Fines                               → FormXXI
                36, // Register of Deductions for Damage or Loss       → FormXX
                37, // Register of Advances                            → FormXXII
                11, // Form 25 - Register of Accidents                  → Form26/Form26A
                34, // Register of Accidents and Dangerous Occurrences  → Form26/Form26A
                58, // Dangerous Occurrence Report                       → Form26A
            ])
            ->update(['is_automatable' => true]);
    }

    public function down(): void
    {
        Schema::table('compliance_manual_master', function (Blueprint $table) {
            $table->dropColumn('is_automatable');
        });
    }
};
