<?php

namespace App\Services\Compliance;

class CsvColumnMapper
{
    private const ALIASES = [

        // ─────────────────────────────────────────────────────────────────────
        // EMPLOYEES
        // ─────────────────────────────────────────────────────────────────────
        'employees' => [
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'employee_id', 'staff_code', 'worker_code', 'emp_no', 'employee_no', 'empid',
            ],
            'name' => [
                'name', 'employee_name', 'emp_name', 'full_name', 'worker_name',
                'employee_full_name', 'emp_full_name',
            ],
            'father_name' => [
                'father_name', 'fathers_name', 'father', 'fathername', 'guardian_name',
                'father_husband_name', 'fatherguardian_name', 's_w_d_of',
                'son_of', 'wife_of', 'daughter_of',
            ],
            'gender' => ['gender', 'sex', 'employee_gender'],
            'date_of_birth' => [
                'date_of_birth', 'dob', 'birth_date', 'birthdate', 'date_birth',
                'employee_dob', 'emp_dob',
            ],
            'marital_status' => ['marital_status', 'marital', 'marriage_status', 'married'],
            'nationality'    => ['nationality', 'nation', 'citizenship'],
            'mobile' => [
                'mobile', 'mobile_no', 'phone', 'contact', 'phone_number',
                'mobile_number', 'contact_no', 'contact_number', 'cell', 'cell_no',
            ],
            'email' => ['email', 'email_id', 'email_address', 'emp_email'],
            'permanent_address' => [
                'permanent_address', 'address', 'addr', 'residence',
                'home_address', 'perm_address', 'permanent_addr',
            ],
            'local_address' => [
                'local_address', 'present_address', 'current_address',
                'local_addr', 'temp_address', 'temporary_address',
            ],
            'designation' => [
                'designation', 'post', 'position', 'job_title', 'role',
                'job_role', 'job_position', 'trade', 'nature_of_work',
            ],
            'department' => [
                'department', 'dept', 'division', 'section', 'unit',
                'dept_name', 'department_name',
            ],
            'skill_type' => [
                'skill_type', 'skill', 'category', 'worker_category', 'emp_category',
                'employment_category', 'worker_type', 'type_of_worker',
                'skilled', 'unskilled', 'semi_skilled',
            ],
            'employment_type' => [
                'employment_type', 'emp_type', 'employee_type', 'contract_type',
                'type_of_employment', 'employment_category',
            ],
            'education_level' => [
                'education_level', 'education', 'qualification', 'edu_level',
                'educational_qualification',
            ],
            'identification_mark' => [
                'identification_mark', 'id_mark', 'identifying_mark', 'id_marks',
            ],
            'work_nature' => [
                'work_nature', 'nature_of_work', 'work_type', 'job_nature',
                'type_of_work', 'work_category',
            ],
            'shift_name' => [
                'shift_name', 'shift', 'shift_type', 'work_shift',
            ],
            'date_of_joining' => [
                'date_of_joining', 'joining_date', 'doj', 'join_date', 'date_joined',
                'date_of_appointment', 'appointment_date', 'joining', 'emp_doj',
            ],
            'date_of_exit' => [
                'date_of_exit', 'exit_date', 'doe', 'leaving_date', 'termination_date',
                'resignation_date', 'date_of_leaving', 'last_working_day',
            ],
            'pf_number' => [
                'pf_number', 'pf_no', 'epf_no', 'pf', 'epf',
                'pf_account', 'pf_account_no', 'provident_fund_no',
            ],
            'uan_number' => [
                'uan_number', 'uan', 'uan_no', 'uan_account',
                'universal_account_number', 'uan_account_no',
            ],
            'esi_number' => [
                'esi_number', 'esic_ip', 'esi_ip', 'esi_no', 'esic_no', 'esi',
                'esic_number', 'esi_ip_no', 'esic_ip_no', 'esi_code',
            ],
            'pan' => ['pan', 'pan_number', 'pan_no', 'pan_card', 'pan_card_no'],
            'aadhaar' => [
                'aadhaar', 'aadhar', 'aadhaar_number', 'aadhar_no', 'aadhaar_no',
                'aadhar_number', 'aadhar_card', 'aadhaar_card',
            ],
            'bank_account' => [
                'bank_account', 'account_number', 'bank_acc', 'acc_no', 'account_no',
                'bank_account_no', 'bank_account_number', 'ac_no',
            ],
            'bank_name'  => ['bank_name', 'bank', 'bank_branch', 'bank_branch_name'],
            'ifsc'       => ['ifsc', 'ifsc_code', 'ifsc_no', 'bank_ifsc', 'ifsc_number'],
            'basic_salary' => [
                'basic_salary', 'basic', 'salary', 'basic_wage', 'ctc',
                'basic_wages', 'base_salary', 'basic_pay',
            ],
            'status' => ['status', 'emp_status', 'employee_status', 'active_status'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // ATTENDANCE
        // ─────────────────────────────────────────────────────────────────────
        'attendance' => [
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no', 'empid',
            ],
            'attendance_date' => [
                'attendance_date', 'date', 'month_date', 'att_date', 'work_date',
            ],
            'status' => [
                'status', 'attendance_status', 'att_status', 'day_status',
            ],
            'shift_name' => ['shift_name', 'shift', 'shift_type', 'work_shift'],
            'in_time'    => ['in_time', 'time_in', 'entry_time', 'punch_in'],
            'out_time'   => ['out_time', 'time_out', 'exit_time', 'punch_out'],
            'working_hours' => [
                'working_hours', 'work_hours', 'hours_worked', 'total_hours',
            ],
            'overtime_hours' => [
                'overtime_hours', 'ot_hours', 'overtime', 'ot',
                'overtime_hrs', 'ot_hour', 'extra_hours',
            ],
            'leave_type' => [
                'leave_type', 'leave', 'leave_category', 'type_of_leave',
            ],
            'weekly_off' => [
                'weekly_off', 'week_off', 'wo', 'weekly_holiday',
            ],
            'holiday_flag' => [
                'holiday_flag', 'holiday', 'is_holiday', 'national_holiday',
            ],
            'remarks' => ['remarks', 'note', 'notes', 'comment'],
            // Legacy summary-mode fields (backward compat)
            'working_days' => [
                'working_days', 'total_days', 'days_worked', 'total_working_days',
                'no_of_working_days', 'payable_days',
            ],
            'present_days' => [
                'present_days', 'present', 'days_present', 'attended_days',
                'no_of_present_days', 'days_attended',
            ],
            'absent_days' => [
                'absent_days', 'absent', 'days_absent', 'loss_of_pay', 'lop',
                'no_of_absent_days', 'days_absent_lwp',
            ],
            'paid_leave' => ['paid_leave', 'pl', 'earned_leave', 'el', 'paid_leaves'],
            'paid_holidays' => ['paid_holidays', 'holidays', 'national_holidays', 'nh'],
            'attendance_month' => ['attendance_month', 'month', 'salary_month', 'att_month'],
            'attendance_year'  => ['attendance_year', 'year', 'salary_year', 'att_year'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // PAYROLL
        // ─────────────────────────────────────────────────────────────────────
        'payroll' => [
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no', 'empid',
            ],
            'payroll_cycle'  => ['payroll_cycle', 'cycle', 'cycle_name'],
            'salary_month'   => ['salary_month', 'month', 'pay_month', 'att_month'],
            'salary_year'    => ['salary_year', 'year', 'pay_year', 'att_year'],
            'total_days_worked' => [
                'total_days_worked', 'payable_days', 'working_days', 'days_worked',
                'paid_days', 'total_days', 'days_payable', 'no_of_days',
            ],
            'paid_leave_days' => [
                'paid_leave_days', 'paid_leave', 'pl', 'earned_leave', 'el',
            ],
            'unpaid_leave_days' => [
                'unpaid_leave_days', 'absent', 'absent_days', 'loss_of_pay',
                'lop', 'lwp', 'days_absent', 'unpaid_leave',
            ],
            'basic_earned' => [
                'basic_earned', 'basic_salary', 'basic', 'basic_wage', 'basic_wages',
                'earned_basic', 'basic_pay', 'basic_amount',
            ],
            'da_earned'  => ['da_earned', 'da', 'dearness_allowance', 'da_amount'],
            'hra_earned' => ['hra_earned', 'hra', 'house_rent_allowance', 'hra_amount'],
            'other_allowances' => [
                'other_allowances', 'allowance', 'allowances', 'special_allowance',
                'other_pay', 'misc_allowance', 'other_allowance', 'additional_allowance',
            ],
            'gross_salary' => [
                'gross_salary', 'gross', 'gross_wage', 'gross_wages', 'total_earnings',
                'gross_pay', 'total_wages', 'gross_amount', 'total_salary',
            ],
            'overtime_hours' => [
                'overtime_hours', 'ot_hours', 'overtime_hrs', 'ot',
                'overtime_hour', 'ot_hour', 'extra_hours',
            ],
            'overtime_wages' => [
                'overtime_wages', 'overtime', 'ot_wages', 'ot_amount',
                'overtime_amount', 'ot_pay', 'overtime_pay',
            ],
            'bonus_amount' => ['bonus_amount', 'bonus', 'bonus_pay'],
            'pf_employee'  => [
                'pf_employee', 'pf', 'epf', 'pf_deduction', 'pf_amount',
                'epf_employee', 'pf_contribution', 'epf_deduction',
            ],
            'pf_employer'  => [
                'pf_employer', 'epf_employer', 'employer_pf', 'employer_epf',
                'pf_employer_contribution',
            ],
            'esi_employee' => [
                'esi_employee', 'esi', 'esic', 'esi_deduction', 'esi_amount',
                'esic_employee', 'esi_contribution', 'esic_deduction',
            ],
            'esi_employer' => [
                'esi_employer', 'esic_employer', 'employer_esi', 'employer_esic',
                'esi_employer_contribution',
            ],
            'professional_tax' => [
                'professional_tax', 'pt', 'prof_tax', 'p_tax', 'ptax',
                'profession_tax',
            ],
            'lwf' => ['lwf', 'labour_welfare_fund', 'lw_fund', 'lwf_amount', 'labor_welfare_fund'],
            'fines'       => ['fines', 'fine', 'fine_amount', 'penalty'],
            'fine_reason' => ['fine_reason', 'reason_for_fine', 'fine_cause'],
            'fine_date'   => ['fine_date', 'date_of_fine', 'fine_imposed_date'],
            'advances'    => ['advances', 'advance', 'advance_amount', 'salary_advance'],
            'advance_reason'      => ['advance_reason', 'purpose_of_advance', 'advance_purpose'],
            'advance_installment' => ['advance_installment', 'installment', 'emi', 'monthly_installment'],
            'other_deductions'    => [
                'other_deductions', 'deductions', 'other_deduct', 'misc_deduction',
                'other_deduction', 'misc_deductions',
            ],
            'deduction_reason'   => ['deduction_reason', 'reason_for_deduction', 'deduction_cause'],
            'damage_particulars' => ['damage_particulars', 'damage', 'loss_particulars', 'particulars'],
            'showed_cause'       => ['showed_cause', 'cause_shown', 'show_cause'],
            'heard_by'           => ['heard_by', 'authority', 'heard_by_officer'],
            'witness_name'       => ['witness_name', 'witness', 'witness_person'],
            'total_deductions'   => [
                'total_deductions', 'total_deduction', 'deduction_total', 'total_deduct',
            ],
            'net_salary' => [
                'net_salary', 'net', 'net_pay', 'net_payment', 'take_home',
                'net_wages', 'net_amount', 'take_home_salary', 'net_wage',
            ],
            'payment_date' => ['payment_date', 'pay_date', 'salary_date', 'paid_on'],
            'payment_mode' => [
                'payment_mode', 'pay_mode', 'mode_of_payment', 'payment_method',
                'payment_type',
            ],
            'transaction_reference' => [
                'transaction_reference', 'txn_ref', 'transaction_id', 'utr', 'ref_no',
            ],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // BONUS
        // ─────────────────────────────────────────────────────────────────────
        'bonus' => [
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no', 'empid',
            ],
            'financial_year'   => ['financial_year', 'fin_year', 'fy', 'year'],
            'bonus_percentage' => ['bonus_percentage', 'bonus_pct', 'bonus_percent', 'percentage'],
            'bonus_amount'     => ['bonus_amount', 'bonus', 'amount', 'bonus_pay'],
            'payment_date'     => ['payment_date', 'pay_date', 'paid_on', 'bonus_date'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // FINES
        // ─────────────────────────────────────────────────────────────────────
        'fines' => [
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no', 'empid',
            ],
            'fine_reason'  => ['fine_reason', 'reason', 'reason_for_fine', 'cause', 'act_or_omission'],
            'fine_date'    => ['fine_date', 'date', 'date_of_fine', 'offence_date'],
            'amount'       => ['amount', 'fine_amount', 'penalty', 'fine'],
            'showed_cause' => ['showed_cause', 'cause_shown', 'show_cause'],
            'heard_by'     => ['heard_by', 'authority', 'heard_by_officer'],
            'witness_name' => ['witness_name', 'witness', 'witness_person'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // ADVANCES
        // ─────────────────────────────────────────────────────────────────────
        'advances' => [
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no', 'empid',
            ],
            'advance_date'        => ['advance_date', 'date', 'loan_date', 'advance_given_date'],
            'advance_amount'      => ['advance_amount', 'amount', 'loan_amount', 'advance'],
            'purpose'             => ['purpose', 'reason', 'advance_purpose', 'advance_reason'],
            'installment_count'   => ['installment_count', 'num_instalments', 'instalments', 'emi_count'],
            'monthly_installment' => ['monthly_installment', 'installment', 'emi', 'monthly_emi'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // DEDUCTIONS
        // ─────────────────────────────────────────────────────────────────────
        'deductions' => [
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no', 'empid',
            ],
            'deduction_date'     => ['deduction_date', 'date', 'deduct_date'],
            'deduction_type'     => ['deduction_type', 'type', 'deduction_category', 'category'],
            'damage_particulars' => ['damage_particulars', 'particulars', 'damage', 'description'],
            'amount'             => ['amount', 'deduction_amount', 'deduct_amount'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // INCIDENTS
        // ─────────────────────────────────────────────────────────────────────
        'incidents' => [
            'incident_date'     => ['incident_date', 'date', 'accident_date', 'event_date'],
            'employee_code'     => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no', 'empid',
            ],
            'location'          => ['location', 'place', 'site', 'incident_location'],
            'injury_type'       => ['injury_type', 'injury', 'type_of_injury', 'nature_of_injury'],
            'severity'          => ['severity', 'severity_level', 'risk_level'],
            'root_cause'        => ['root_cause', 'cause', 'reason', 'root_cause_analysis'],
            'corrective_action' => ['corrective_action', 'corrective', 'action_taken'],
            'preventive_action' => ['preventive_action', 'preventive', 'prevention_measure'],
            'medical_leave_days'=> ['medical_leave_days', 'leave_days', 'medical_leave', 'days_off'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // CONTRACTORS
        // ─────────────────────────────────────────────────────────────────────
        'contractors' => [
            'contractor_name' => ['contractor_name', 'name', 'company_name', 'firm_name', 'contractor'],
            'contractor_code' => ['contractor_code', 'code', 'vendor_code', 'contractor_id'],
            'license_number'  => ['license_number', 'licence_number', 'license_no', 'licence_no', 'clra_license', 'registration_no'],
            'valid_from'      => ['valid_from', 'license_from', 'licence_from', 'issue_date', 'start_date'],
            'valid_to'        => ['valid_to', 'license_expiry', 'licence_expiry', 'expiry_date', 'valid_upto', 'validity', 'end_date'],
            'nature_of_work'  => ['nature_of_work', 'work_nature', 'type_of_work', 'work_type', 'scope_of_work'],
            'contact_person'  => ['contact_person', 'contact', 'representative', 'authorized_person'],
            'mobile'          => ['mobile', 'mobile_no', 'phone', 'contact_no', 'phone_number'],
            'email'           => ['email', 'email_id', 'email_address'],
            'address'         => ['address', 'contractor_address', 'office_address', 'registered_address'],
            'max_workers'     => ['max_workers', 'max_workmen', 'worker_limit', 'strength', 'no_of_workers'],
            'pf_code'         => ['pf_code', 'pf_number', 'epf_code', 'pf_registration'],
            'esi_code'        => ['esi_code', 'esi_number', 'esic_code', 'esi_registration'],
        ],

        // ─────────────────────────────────────────────────────────────────────
        // HAZARD REGISTER
        // ─────────────────────────────────────────────────────────────────────
        'hazard_register' => [
            'hazard_type'       => ['hazard_type', 'type', 'hazard', 'hazard_category'],
            'location'          => ['location', 'place', 'site', 'hazard_location'],
            'risk_rating'       => ['risk_rating', 'risk', 'risk_level', 'rating'],
            'control_measure'   => ['control_measure', 'control', 'controls', 'control_measures'],
            'corrective_action' => ['corrective_action', 'corrective', 'action_taken'],
            'reported_by'       => ['reported_by', 'reporter', 'reported_by_person', 'raised_by'],
        ],
    ];

    public static function buildLookup(string $type): array
    {
        $lookup = [];
        foreach (self::ALIASES[$type] ?? [] as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                $lookup[$alias] = $canonical;
            }
        }
        return $lookup;
    }

    public static function mapHeaders(array $rawHeaders, string $type, array &$skipped = []): array
    {
        $lookup  = self::buildLookup($type);
        $mapping = [];

        foreach ($rawHeaders as $index => $raw) {
            $normalised = strtolower(trim(preg_replace('/[\s\-\/]+/', '_', $raw)));
            $normalised = preg_replace('/[^a-z0-9_]/', '', $normalised);

            if (isset($lookup[$normalised])) {
                $canonical = $lookup[$normalised];
                if (! isset($mapping[$canonical])) {
                    $mapping[$canonical] = $index;
                }
            } else {
                $skipped[] = $raw;
            }
        }

        return $mapping;
    }

    public static function extractRow(array $rawData, array $headerMapping): array
    {
        $row = [];
        foreach ($headerMapping as $canonical => $index) {
            $row[$canonical] = isset($rawData[$index]) ? trim($rawData[$index]) : '';
        }
        return $row;
    }

    public static function requiredFields(string $type): array
    {
        return match ($type) {
            'employees'      => ['employee_code', 'name'],
            'payroll'        => ['employee_code', 'gross_salary', 'net_salary'],
            'attendance'     => ['employee_code'],
            'bonus'          => ['employee_code', 'financial_year', 'bonus_amount'],
            'fines'          => ['employee_code', 'fine_date', 'amount'],
            'advances'       => ['employee_code', 'advance_date', 'advance_amount'],
            'deductions'     => ['employee_code', 'deduction_date', 'amount'],
            'incidents'      => ['incident_date'],
            'hazard_register'=> ['hazard_type', 'location'],
            'contractors'    => ['contractor_name', 'license_number'],
            default          => [],
        };
    }

    public static function knownFields(string $type): array
    {
        return array_keys(self::ALIASES[$type] ?? []);
    }

    public static function supportedTypes(): array
    {
        return array_keys(self::ALIASES);
    }
}
