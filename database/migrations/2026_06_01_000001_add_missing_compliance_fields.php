<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── workforce_employee: missing statutory fields ───────────────────────
        if (Schema::hasTable('workforce_employee')) {
            Schema::table('workforce_employee', function (Blueprint $table) {
                $add = [
                    'employment_type'     => fn() => $table->string('employment_type', 30)->nullable()->comment('Permanent/Temporary/Contract')->after('status'),
                    'education_level'     => fn() => $table->string('education_level', 50)->nullable()->after('employment_type'),
                    'identification_mark' => fn() => $table->string('identification_mark', 100)->nullable()->after('education_level'),
                    'work_nature'         => fn() => $table->string('work_nature', 100)->nullable()->comment('Nature of work / trade')->after('identification_mark'),
                    'shift_name'          => fn() => $table->string('shift_name', 50)->nullable()->after('work_nature'),
                ];
                foreach ($add as $col => $def) {
                    if (! Schema::hasColumn('workforce_employee', $col)) $def();
                }
            });
        }

        // ── branches: geo fields required by statutory forms ──────────────────
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                $add = [
                    'district' => fn() => $table->string('district', 100)->nullable()->after('address'),
                    'state'    => fn() => $table->string('state', 100)->nullable()->after('district'),
                    'pin_code' => fn() => $table->string('pin_code', 10)->nullable()->after('state'),
                ];
                foreach ($add as $col => $def) {
                    if (! Schema::hasColumn('branches', $col)) $def();
                }
            });
        }

        // ── workforce_payroll_entry: employer-side contributions ───────────────
        if (Schema::hasTable('workforce_payroll_entry')) {
            Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                $add = [
                    'pf_employer'          => fn() => $table->decimal('pf_employer', 12, 2)->default(0)->after('pf_employee'),
                    'esi_employer'         => fn() => $table->decimal('esi_employer', 12, 2)->default(0)->after('esi_employee'),
                    'lwf'                  => fn() => $table->decimal('lwf', 12, 2)->default(0)->after('professional_tax'),
                    'bonus_amount'         => fn() => $table->decimal('bonus_amount', 12, 2)->default(0)->after('lwf'),
                    'salary_month'         => fn() => $table->unsignedTinyInteger('salary_month')->nullable()->after('bonus_amount'),
                    'salary_year'          => fn() => $table->unsignedSmallInteger('salary_year')->nullable()->after('salary_month'),
                    'transaction_reference'=> fn() => $table->string('transaction_reference', 100)->nullable()->after('payment_mode'),
                    'fine_reason'          => fn() => $table->string('fine_reason', 200)->nullable()->after('fines'),
                    'fine_date'            => fn() => $table->date('fine_date')->nullable()->after('fine_reason'),
                    'advance_reason'       => fn() => $table->string('advance_reason', 200)->nullable()->after('advances'),
                    'advance_installment'  => fn() => $table->decimal('advance_installment', 12, 2)->default(0)->after('advance_reason'),
                    'deduction_reason'     => fn() => $table->string('deduction_reason', 200)->nullable()->after('other_deductions'),
                    'damage_particulars'   => fn() => $table->string('damage_particulars', 200)->nullable()->after('deduction_reason'),
                    'showed_cause'         => fn() => $table->boolean('showed_cause')->default(false)->after('damage_particulars'),
                    'heard_by'             => fn() => $table->string('heard_by', 100)->nullable()->after('showed_cause'),
                    'witness_name'         => fn() => $table->string('witness_name', 100)->nullable()->after('heard_by'),
                ];
                foreach ($add as $col => $def) {
                    if (! Schema::hasColumn('workforce_payroll_entry', $col)) $def();
                }
            });
        }

        // ── workforce_attendance: per-day detail fields ───────────────────────
        if (Schema::hasTable('workforce_attendance')) {
            Schema::table('workforce_attendance', function (Blueprint $table) {
                $add = [
                    'in_time'      => fn() => $table->time('in_time')->nullable()->after('status'),
                    'out_time'     => fn() => $table->time('out_time')->nullable()->after('in_time'),
                    'working_hours'=> fn() => $table->decimal('working_hours', 5, 2)->default(0)->after('out_time'),
                    'shift_name'   => fn() => $table->string('shift_name', 50)->nullable()->after('working_hours'),
                    'leave_type'   => fn() => $table->string('leave_type', 30)->nullable()->after('shift_name'),
                    'weekly_off'   => fn() => $table->boolean('weekly_off')->default(false)->after('leave_type'),
                    'holiday_flag' => fn() => $table->boolean('holiday_flag')->default(false)->after('weekly_off'),
                    'remarks'      => fn() => $table->string('remarks', 200)->nullable()->after('holiday_flag'),
                ];
                foreach ($add as $col => $def) {
                    if (! Schema::hasColumn('workforce_attendance', $col)) $def();
                }
            });
        }

        // ── workforce_fines: audit trail fields ───────────────────────────────
        if (Schema::hasTable('workforce_fines')) {
            Schema::table('workforce_fines', function (Blueprint $table) {
                $add = [
                    'showed_cause' => fn() => $table->boolean('showed_cause')->default(false)->after('reason'),
                    'heard_by'     => fn() => $table->string('heard_by', 100)->nullable()->after('showed_cause'),
                    'witness_name' => fn() => $table->string('witness_name', 100)->nullable()->after('heard_by'),
                ];
                foreach ($add as $col => $def) {
                    if (! Schema::hasColumn('workforce_fines', $col)) $def();
                }
            });
        }

        // ── workforce_advances: purpose field ─────────────────────────────────
        if (Schema::hasTable('workforce_advances')) {
            Schema::table('workforce_advances', function (Blueprint $table) {
                if (! Schema::hasColumn('workforce_advances', 'purpose')) {
                    $table->string('purpose', 200)->nullable()->after('amount');
                }
                if (! Schema::hasColumn('workforce_advances', 'monthly_installment')) {
                    $table->decimal('monthly_installment', 12, 2)->default(0)->after('num_instalments');
                }
            });
        }

        // ── workforce_deductions: type field ──────────────────────────────────
        if (Schema::hasTable('workforce_deductions')) {
            Schema::table('workforce_deductions', function (Blueprint $table) {
                if (! Schema::hasColumn('workforce_deductions', 'deduction_type')) {
                    $table->string('deduction_type', 50)->nullable()->after('deduction_date');
                }
            });
        }

        // ── hazard_register: missing fields ───────────────────────────────────
        if (Schema::hasTable('hazard_register')) {
            Schema::table('hazard_register', function (Blueprint $table) {
                $add = [
                    'risk_rating'       => fn() => $table->string('risk_rating', 20)->nullable()->after('severity'),
                    'control_measure'   => fn() => $table->text('control_measure')->nullable()->after('risk_rating'),
                    'preventive_action' => fn() => $table->text('preventive_action')->nullable()->after('corrective_action'),
                    'reported_by'       => fn() => $table->string('reported_by', 100)->nullable()->after('preventive_action'),
                ];
                foreach ($add as $col => $def) {
                    if (! Schema::hasColumn('hazard_register', $col)) $def();
                }
            });
        }

        // ── incidents: medical_leave_days + root_cause ────────────────────────
        if (Schema::hasTable('incidents')) {
            Schema::table('incidents', function (Blueprint $table) {
                $add = [
                    'root_cause'        => fn() => $table->text('root_cause')->nullable()->after('cause'),
                    'corrective_action' => fn() => $table->text('corrective_action')->nullable()->after('root_cause'),
                    'preventive_action' => fn() => $table->text('preventive_action')->nullable()->after('corrective_action'),
                    'medical_leave_days'=> fn() => $table->unsignedSmallInteger('medical_leave_days')->default(0)->after('preventive_action'),
                ];
                foreach ($add as $col => $def) {
                    if (! Schema::hasColumn('incidents', $col)) $def();
                }
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'workforce_employee'      => ['employment_type', 'education_level', 'identification_mark', 'work_nature', 'shift_name'],
            'branches'                => ['district', 'state', 'pin_code'],
            'workforce_payroll_entry' => ['pf_employer', 'esi_employer', 'lwf', 'bonus_amount', 'salary_month', 'salary_year', 'transaction_reference', 'fine_reason', 'fine_date', 'advance_reason', 'advance_installment', 'deduction_reason', 'damage_particulars', 'showed_cause', 'heard_by', 'witness_name'],
            'workforce_attendance'    => ['in_time', 'out_time', 'working_hours', 'shift_name', 'leave_type', 'weekly_off', 'holiday_flag', 'remarks'],
            'workforce_fines'         => ['showed_cause', 'heard_by', 'witness_name'],
            'workforce_advances'      => ['purpose', 'monthly_installment'],
            'workforce_deductions'    => ['deduction_type'],
            'hazard_register'         => ['risk_rating', 'control_measure', 'preventive_action', 'reported_by'],
            'incidents'               => ['root_cause', 'corrective_action', 'preventive_action', 'medical_leave_days'],
        ];

        foreach ($drops as $table => $cols) {
            if (! Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($table, $cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn($table, $col)) $t->dropColumn($col);
                }
            });
        }
    }
};
