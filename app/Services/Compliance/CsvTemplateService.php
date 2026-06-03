<?php

namespace App\Services\Compliance;

class CsvTemplateService
{
    /**
     * Metadata: which forms each template feeds.
     */
    private const METADATA = [
        'employees' => [
            'filename' => 'sample_employees.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_B','FORM_XII','FORM_XIII','FORM_XIV','FORM_XVI','FORM_XVII','FORM_XIX','FORM_XX','FORM_XXI','FORM_XXII','FORM_XXIII','FORM_25','FORM_A','FORM_C','FORM_D'],
            'required' => ['employee_code','name'],
        ],
        'attendance' => [
            'filename' => 'sample_attendance.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_B','FORM_XVI','FORM_25','FORM_XXIII'],
            'required' => ['employee_code'],
        ],
        'payroll' => [
            'filename' => 'sample_payroll.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_B','FORM_XX','FORM_XXI','FORM_XXII','FORM_XXIII','FORM_XVII','FORM_A','FORM_C','FORM_D'],
            'required' => ['employee_code','gross_salary','net_salary'],
        ],
        'bonus' => [
            'filename' => 'sample_bonus.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_C','FORM_D'],
            'required' => ['employee_code','financial_year','bonus_amount'],
        ],
        'fines' => [
            'filename' => 'sample_fines.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_XX','SHOPS_FINES'],
            'required' => ['employee_code','fine_date','amount'],
        ],
        'advances' => [
            'filename' => 'sample_advances.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_XXII','SHOPS_UNPAID'],
            'required' => ['employee_code','advance_date','advance_amount'],
        ],
        'deductions' => [
            'filename' => 'sample_deductions.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_XX','FORM_XXI'],
            'required' => ['employee_code','deduction_date','amount'],
        ],
        'incidents' => [
            'filename' => 'sample_incidents.csv',
            'version'  => '2.0',
            'forms'    => ['FORM_11','FORM_18','FORM_26','ESI_FORM_12'],
            'required' => ['incident_date'],
        ],
        'hazard_register' => [
            'filename' => 'sample_hazard_register.csv',
            'version'  => '1.0',
            'forms'    => ['HAZARD_REG'],
            'required' => ['hazard_type','location'],
        ],
        'contractors' => [
            'filename' => 'sample_contractors.csv',
            'version'  => '1.0',
            'forms'    => ['FORM_XII','FORM_XIII','FORM_XIV','FORM_XVI','FORM_XVII'],
            'required' => ['contractor_name','license_number'],
        ],
    ];

    private const TEMPLATES = [
        'employees' => [
            'headers' => 'employee_code,name,father_name,gender,date_of_birth,marital_status,nationality,mobile,email,permanent_address,local_address,designation,department,skill_type,employment_type,education_level,identification_mark,work_nature,shift_name,date_of_joining,date_of_exit,pf_number,uan_number,esi_number,pan,aadhaar,bank_account,bank_name,ifsc,basic_salary,status',
            'rows' => [
                'EMP001,Arumugam S,Subramaniam A,M,1985-06-15,Married,Indian,9876543210,arumugam@example.com,12 Main Road Chennai TN 600001,12 Main Road Chennai TN 600001,Supervisor,Production,Skilled,Permanent,SSLC,Scar on left hand,Machine Operation,General,2020-01-10,,TN/CHN/12345/001,100123456789,1234567890,ABCDE1234F,123456789012,12345678901234,State Bank of India,SBIN0001234,18000,active',
                'EMP002,Balamurugan K,Krishnan B,M,1990-03-22,Single,Indian,9876543211,bala@example.com,45 Anna Nagar Chennai TN 600040,45 Anna Nagar Chennai TN 600040,Technician,Maintenance,Semi-Skilled,Permanent,HSC,Mole on right cheek,Equipment Maintenance,General,2021-04-01,,TN/CHN/12345/002,100123456790,1234567891,FGHIJ5678K,234567890123,12345678901235,Indian Bank,IDIB000M123,15000,active',
                'EMP003,Kavitha R,Rajan P,F,1992-11-08,Married,Indian,9876543212,kavitha@example.com,78 T Nagar Chennai TN 600017,78 T Nagar Chennai TN 600017,Clerk,Administration,Unskilled,Permanent,Graduate,Birthmark on neck,Data Entry,General,2022-07-15,,TN/CHN/12345/003,100123456791,1234567892,KLMNO9012L,345678901234,12345678901236,Canara Bank,CNRB0001234,12000,active',
            ],
        ],
        'attendance' => [
            'headers' => 'employee_code,attendance_date,status,shift_name,in_time,out_time,working_hours,overtime_hours,leave_type,weekly_off,holiday_flag,remarks',
            'rows' => [
                'EMP001,2025-01-01,present,General,09:00,18:00,8.00,0,,0,0,',
                'EMP001,2025-01-02,present,General,09:00,20:00,8.00,2,,0,0,OT approved',
                'EMP001,2025-01-03,absent,General,,,0,0,,0,0,',
                'EMP001,2025-01-04,leave,General,,,0,0,CL,0,0,Casual Leave',
                'EMP001,2025-01-05,holiday,General,,,0,0,,0,1,Republic Day',
                'EMP002,2025-01-01,present,General,09:00,18:00,8.00,0,,0,0,',
                'EMP002,2025-01-02,half_day,General,09:00,13:00,4.00,0,,0,0,',
                'EMP003,2025-01-01,present,General,09:00,18:00,8.00,0,,0,0,',
            ],
        ],
        'payroll' => [
            'headers' => 'employee_code,salary_month,salary_year,total_days_worked,paid_leave_days,unpaid_leave_days,basic_earned,da_earned,hra_earned,other_allowances,gross_salary,overtime_hours,overtime_wages,bonus_amount,pf_employee,pf_employer,esi_employee,esi_employer,professional_tax,lwf,fines,fine_reason,fine_date,advances,advance_reason,advance_installment,other_deductions,deduction_reason,damage_particulars,showed_cause,heard_by,witness_name,total_deductions,net_salary,payment_date,payment_mode,transaction_reference',
            'rows' => [
                'EMP001,1,2025,26,0,0,9000,900,3600,1500,18000,2,500,0,1080,1080,135,473,200,20,0,,,0,,,0,,,0,,,2915,15085,2025-01-31,Bank Transfer,UTR123456',
                'EMP002,1,2025,25,1,0,7500,750,3000,1250,15000,4,1000,0,900,900,113,394,200,20,500,Late attendance,2025-01-15,0,,,0,,,1,Manager,Rajan,2127,13873,2025-01-31,Bank Transfer,UTR123457',
                'EMP003,1,2025,24,0,2,6000,600,2400,1000,12000,0,0,0,720,720,90,315,200,20,0,,,1000,Medical emergency,,0,,,0,,,2045,9955,2025-01-31,Bank Transfer,UTR123458',
            ],
        ],
        'bonus' => [
            'headers' => 'employee_code,employee_name,father_name,department,designation,financial_year,bonus_percentage,bonus_amount,payment_date,remarks',
            'rows' => [
                'EMP001,Arumugam S,Subramaniam A,Production,Supervisor,2024-25,8.33,1500,2025-03-31,Approved',
                'EMP002,Balamurugan K,Krishnan B,Maintenance,Technician,2024-25,8.33,1250,2025-03-31,Approved',
                'EMP003,Kavitha R,Rajan P,Administration,Clerk,2024-25,8.33,1000,2025-03-31,Approved',
            ],
        ],
        'fines' => [
            'headers' => 'employee_code,employee_name,father_name,department,designation,pf_number,esi_number,fine_date,fine_reason,amount,showed_cause,heard_by,witness_name,remarks',
            'rows' => [
                'EMP002,Balamurugan K,Krishnan B,Maintenance,Technician,100123456790,1234567891,2025-01-15,Late attendance,500,yes,Production Manager,Rajan K,',
                'EMP003,Kavitha R,Rajan P,Administration,Clerk,100123456791,1234567892,2025-01-20,Equipment damage,250,yes,HR Manager,Suresh P,',
            ],
        ],
        'advances' => [
            'headers' => 'employee_code,employee_name,father_name,department,designation,pf_number,esi_number,advance_date,advance_amount,purpose,installment_count,monthly_installment,remarks',
            'rows' => [
                'EMP003,Kavitha R,Rajan P,Administration,Clerk,100123456791,1234567892,2025-01-10,5000,Medical,5,1000,Approved',
                'EMP001,Arumugam S,Subramaniam A,Production,Supervisor,100123456789,1234567890,2025-01-15,3000,Personal,3,1000,Approved',
            ],
        ],
        'deductions' => [
            'headers' => 'employee_code,employee_name,father_name,department,designation,pf_number,esi_number,deduction_date,deduction_type,damage_particulars,amount,remarks',
            'rows' => [
                'EMP002,Balamurugan K,Krishnan B,Maintenance,Technician,100123456790,1234567891,2025-01-20,Damage,Broken equipment,800,Approved',
                'EMP003,Kavitha R,Rajan P,Administration,Clerk,100123456791,1234567892,2025-01-25,Loss,Missing items,500,Approved',
            ],
        ],
        'incidents' => [
            'headers' => 'incident_date,employee_code,employee_name,father_name,department,designation,location,injury_type,severity,root_cause,corrective_action,preventive_action,medical_leave_days,remarks',
            'rows' => [
                '2025-01-12,EMP002,Balamurugan K,Krishnan B,Maintenance,Technician,Production,Minor cut,low,Tool misuse,First aid,Training,2,Closed',
                '2025-01-18,EMP001,Arumugam S,Subramaniam A,Production,Supervisor,Warehouse,Slip,medium,Wet floor,Treatment,Warning signs,5,Closed',
            ],
        ],
        'hazard_register' => [
            'headers' => 'hazard_type,location,risk_rating,control_measure,corrective_action,reported_by',
            'rows' => [
                'Chemical Exposure,Paint Shop,High,PPE mandatory,Ventilation upgrade,Safety Officer',
                'Electrical Hazard,Generator Room,Medium,Restricted access,Wiring repair,Maintenance',
                'Fire Risk,Storage Area,High,Fire extinguishers,Sprinkler system,Safety Officer',
            ],
        ],
        'contractors' => [
            'headers' => 'contractor_name,contractor_code,license_number,valid_from,valid_to,nature_of_work,contact_person,mobile,email,address,max_workers,pf_code,esi_code',
            'rows' => [
                'Murugan Contractors,CON001,LIC/TN/2024/001,2024-01-01,2026-12-31,Civil,Murugan R,9876500001,murugan@contractors.com,Industrial Area Chennai,50,TN/CHN/CON/001,31-00-123456-000',
                'Selvam Labour,CON002,LIC/TN/2024/002,2024-01-01,2026-06-30,Maintenance,Selvam K,9876500002,selvam@labour.com,GST Road Chennai,30,TN/CHN/CON/002,31-00-234567-000',
                'Rajan Engineering,CON003,LIC/TN/2024/003,2024-01-01,2025-12-31,Electrical,Rajan P,9876500003,rajan@engineering.com,SIDCO Chennai,20,TN/CHN/CON/003,31-00-345678-000',
            ],
        ],
    ];

    public function supportedTypes(): array
    {
        return array_keys(self::TEMPLATES);
    }

    public function metadata(string $type): ?array
    {
        return self::METADATA[$type] ?? null;
    }

    public function allMetadata(): array
    {
        return self::METADATA;
    }

    public function filename(string $type): ?string
    {
        return self::METADATA[$type]['filename'] ?? null;
    }

    public function generate(string $type): ?string
    {
        $tpl = self::TEMPLATES[$type] ?? null;
        if (! $tpl) return null;

        $now   = now();
        $y     = $now->year;
        $m     = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $fy    = ($now->month >= 4 ? $y : $y - 1) . '-' . substr(($now->month >= 4 ? $y + 1 : $y), -2);

        $rows = array_map(function (string $row) use ($y, $m, $fy) {
            $row = preg_replace_callback('/\d{4}-\d{2}-\d{2}/', function ($match) use ($y, $m) {
                $day = substr($match[0], 8, 2);
                return "{$y}-{$m}-{$day}";
            }, $row);
            $row = preg_replace('/\d{4}-\d{2}(?=,|$)/', $fy, $row);
            return $row;
        }, $tpl['rows']);

        return implode("\n", array_merge([$tpl['headers']], $rows));
    }

    public function downloadResponse(string $type): ?\Illuminate\Http\Response
    {
        $content  = $this->generate($type);
        $filename = $this->filename($type);

        if (! $content || ! $filename) return null;

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store',
        ]);
    }
}
