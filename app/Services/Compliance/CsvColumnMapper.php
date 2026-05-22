<?php

namespace App\Services\Compliance;

class CsvColumnMapper
{
    /**
     * Canonical field → accepted aliases (all lowercase, underscored).
     * The canonical key is always the first alias tried.
     */
    private const ALIASES = [
        'employees' => [
            // ── Required ──────────────────────────────────────────────────────
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'employee_id', 'staff_code', 'worker_code', 'emp_no', 'employee_no',
            ],
            'name' => [
                'name', 'employee_name', 'emp_name', 'full_name', 'worker_name',
                'employee_full_name', 'emp_full_name',
            ],
            // ── Optional ─────────────────────────────────────────────────────
            'father_name' => [
                'father_name', 'fathers_name', 'father', 'fathername', 'guardian_name',
                'father_husband_name', 'fatherguardian_name', 's_w_d_of',
                'son_of', 'wife_of', 'daughter_of',
            ],
            'gender' => [
                'gender', 'sex', 'employee_gender',
            ],
            'date_of_birth' => [
                'date_of_birth', 'dob', 'birth_date', 'birthdate', 'date_birth',
                'employee_dob', 'emp_dob',
            ],
            'designation' => [
                'designation', 'post', 'position', 'job_title', 'role',
                'job_role', 'job_position', 'trade', 'nature_of_work',
            ],
            'department' => [
                'department', 'dept', 'division', 'section', 'unit',
                'dept_name', 'department_name',
            ],
            'mobile' => [
                'mobile', 'mobile_no', 'phone', 'contact', 'phone_number',
                'mobile_number', 'contact_no', 'contact_number', 'cell', 'cell_no',
            ],
            'email' => [
                'email', 'email_id', 'email_address', 'emp_email',
            ],
            'permanent_address' => [
                'permanent_address', 'address', 'addr', 'residence',
                'home_address', 'perm_address', 'permanent_addr',
            ],
            'local_address' => [
                'local_address', 'present_address', 'current_address',
                'local_addr', 'temp_address', 'temporary_address',
            ],
            // uan_number and pf_number are SEPARATE fields — do NOT alias into each other
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
            'pan' => [
                'pan', 'pan_number', 'pan_no', 'pan_card', 'pan_card_no',
            ],
            'aadhaar' => [
                'aadhaar', 'aadhar', 'aadhaar_number', 'aadhar_no', 'aadhaar_no',
                'aadhar_number', 'aadhar_card', 'aadhaar_card',
            ],
            'bank_account' => [
                'bank_account', 'account_number', 'bank_acc', 'acc_no', 'account_no',
                'bank_account_no', 'bank_account_number', 'ac_no',
            ],
            'bank_name' => [
                'bank_name', 'bank', 'bank_branch', 'bank_branch_name',
            ],
            'ifsc' => [
                'ifsc', 'ifsc_code', 'ifsc_no', 'bank_ifsc', 'ifsc_number',
            ],
            'date_of_joining' => [
                'date_of_joining', 'joining_date', 'doj', 'join_date', 'date_joined',
                'date_of_appointment', 'appointment_date', 'joining', 'emp_doj',
            ],
            'date_of_exit' => [
                'date_of_exit', 'exit_date', 'doe', 'leaving_date', 'termination_date',
                'resignation_date', 'date_of_leaving', 'last_working_day',
            ],
            'marital_status' => [
                'marital_status', 'marital', 'marriage_status', 'married',
            ],
            'nationality' => [
                'nationality', 'nation', 'citizenship',
            ],
            'skill_type' => [
                'skill_type', 'skill', 'category', 'worker_category', 'emp_category',
                'employment_category', 'worker_type', 'type_of_worker',
                'skilled', 'unskilled', 'semi_skilled',
            ],
            'basic_salary' => [
                'basic_salary', 'basic', 'salary', 'basic_wage', 'ctc',
                'basic_wages', 'base_salary', 'basic_pay',
            ],
        ],

        'payroll' => [
            // ── Required ──────────────────────────────────────────────────────
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no',
            ],
            'gross_salary' => [
                'gross_salary', 'gross', 'gross_wage', 'gross_wages', 'total_earnings',
                'gross_pay', 'total_wages', 'gross_amount', 'total_salary',
            ],
            'net_salary' => [
                'net_salary', 'net', 'net_pay', 'net_payment', 'take_home',
                'net_wages', 'net_amount', 'take_home_salary', 'net_wage',
            ],
            // ── Optional ─────────────────────────────────────────────────────
            'basic_earned' => [
                'basic_earned', 'basic_salary', 'basic', 'basic_wage', 'basic_wages',
                'earned_basic', 'basic_pay', 'basic_amount',
            ],
            'da_earned' => [
                'da_earned', 'da', 'dearness_allowance', 'da_amount',
                'da_allowance',
            ],
            'hra_earned' => [
                'hra_earned', 'hra', 'house_rent_allowance', 'hra_amount',
                'hra_allowance', 'rent_allowance',
            ],
            'conveyance_allowance' => [
                'conveyance_allowance', 'conveyance', 'transport_allowance',
                'travel_allowance', 'conv',
            ],
            'overtime_wages' => [
                'overtime_wages', 'overtime', 'ot_wages', 'ot_amount',
                'overtime_amount', 'ot_pay', 'overtime_pay',
            ],
            'other_allowances' => [
                'other_allowances', 'allowance', 'allowances', 'bonus',
                'special_allowance', 'other_pay', 'misc_allowance',
                'other_allowance', 'additional_allowance',
            ],
            'total_days_worked' => [
                'total_days_worked', 'payable_days', 'working_days', 'days_worked',
                'paid_days', 'total_days', 'days_payable', 'no_of_days',
            ],
            'unpaid_leave_days' => [
                'unpaid_leave_days', 'absent', 'absent_days', 'loss_of_pay',
                'lop', 'lwp', 'days_absent', 'unpaid_leave',
            ],
            'paid_leave_days' => [
                'paid_leave_days', 'paid_leave', 'pl', 'earned_leave', 'el',
                'casual_leave', 'cl',
            ],
            'pf_employee' => [
                'pf_employee', 'pf', 'epf', 'pf_deduction', 'pf_amount',
                'epf_employee', 'pf_contribution', 'epf_deduction',
            ],
            'esi_employee' => [
                'esi_employee', 'esi', 'esic', 'esi_deduction', 'esi_amount',
                'esic_employee', 'esi_contribution', 'esic_deduction',
            ],
            'professional_tax' => [
                'professional_tax', 'pt', 'prof_tax', 'p_tax', 'ptax',
                'profession_tax', 'professional_tax_deduction',
            ],
            'lwf' => [
                'lwf', 'labour_welfare_fund', 'lw_fund', 'lwf_amount',
                'labor_welfare_fund',
            ],
            'fines' => [
                'fines', 'fine', 'fine_amount', 'penalty',
            ],
            'advances' => [
                'advances', 'advance', 'advance_amount', 'salary_advance',
            ],
            'other_deductions' => [
                'other_deductions', 'deductions', 'other_deduct', 'misc_deduction',
                'other_deduction', 'misc_deductions',
            ],
            'total_deductions' => [
                'total_deductions', 'total_deduction', 'deduction_total', 'total_deduct',
            ],
            'overtime_hours' => [
                'overtime_hours', 'ot_hours', 'overtime_hrs', 'ot',
                'overtime_hour', 'ot_hour',
            ],
            'payment_date' => [
                'payment_date', 'pay_date', 'salary_date', 'paid_on',
                'salary_paid_date',
            ],
            'payment_mode' => [
                'payment_mode', 'pay_mode', 'mode_of_payment', 'payment_method',
                'payment_type',
            ],
        ],

        'attendance' => [
            // ── Required ──────────────────────────────────────────────────────
            'employee_code' => [
                'employee_code', 'emp_code', 'empcode', 'code', 'emp_id',
                'staff_code', 'worker_code', 'emp_no',
            ],
            'working_days' => [
                'working_days', 'total_days', 'days_worked', 'total_working_days',
                'no_of_working_days', 'payable_days',
            ],
            // ── Optional ─────────────────────────────────────────────────────
            'employee_name' => [
                'employee_name', 'name', 'emp_name', 'worker_name',
            ],
            'designation' => [
                'designation', 'post', 'position', 'job_title',
            ],
            'present_days' => [
                'present_days', 'present', 'days_present', 'attended_days',
                'no_of_present_days', 'days_attended',
            ],
            'absent_days' => [
                'absent_days', 'absent', 'days_absent', 'loss_of_pay', 'lop',
                'no_of_absent_days', 'days_absent_lwp',
            ],
            'weekly_off' => [
                'weekly_off', 'weekly_offs', 'week_off', 'wo',
                'no_of_weekly_off', 'weekly_holidays',
            ],
            'paid_leave' => [
                'paid_leave', 'pl', 'earned_leave', 'el',
                'paid_leaves', 'earned_leaves',
            ],
            'paid_holidays' => [
                'paid_holidays', 'holidays', 'national_holidays', 'nh',
                'holiday_days',
            ],
            'overtime_hours' => [
                'overtime_hours', 'ot_hours', 'overtime', 'ot',
                'overtime_hrs', 'ot_hour',
            ],
            'shift' => [
                'shift', 'shift_name', 'shift_type',
            ],
            'attendance_status' => [
                'attendance_status', 'status',
            ],
            'attendance_date' => [
                'attendance_date', 'date', 'month_date', 'att_date',
            ],
            'attendance_month' => [
                'attendance_month', 'month', 'salary_month', 'att_month',
            ],
            'attendance_year' => [
                'attendance_year', 'year', 'salary_year', 'att_year',
            ],
        ],
    ];

    /**
     * Build a reverse lookup: normalised_alias → canonical_field for a given type.
     */
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

    /**
     * Map raw CSV headers to canonical field names.
     * Returns [canonical => csvIndex] for recognised columns.
     * Unknown columns are collected in $skipped.
     */
    public static function mapHeaders(array $rawHeaders, string $type, array &$skipped = []): array
    {
        $lookup  = self::buildLookup($type);
        $mapping = []; // canonical => index

        foreach ($rawHeaders as $index => $raw) {
            $normalised = strtolower(trim(preg_replace('/[\s\-\/]+/', '_', $raw)));
            $normalised = preg_replace('/[^a-z0-9_]/', '', $normalised);

            if (isset($lookup[$normalised])) {
                $canonical = $lookup[$normalised];
                // First occurrence wins (handles duplicate-ish headers)
                if (!isset($mapping[$canonical])) {
                    $mapping[$canonical] = $index;
                }
            } else {
                $skipped[] = $raw;
            }
        }

        return $mapping;
    }

    /**
     * Extract a canonical row from raw CSV data using the header mapping.
     */
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
            'employees'  => ['employee_code', 'name'],
            'payroll'    => ['employee_code', 'gross_salary', 'net_salary'],
            'attendance' => ['employee_code', 'working_days'],
            default      => [],
        };
    }

    public static function knownFields(string $type): array
    {
        return array_keys(self::ALIASES[$type] ?? []);
    }
}
