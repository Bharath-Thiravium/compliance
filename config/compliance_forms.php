<?php

return [
    // FACTORIES ACT FORMS (13)
    'FORM_B' => [
        'table' => 'workforce_payroll_entry',
        'date_field' => null,
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_payroll_entry.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_id' => 'workforce_payroll_entry.employee_id',
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'basic_earned' => 'workforce_payroll_entry.basic_earned',
            'da_earned' => 'workforce_payroll_entry.da_earned',
            'hra_earned' => 'workforce_payroll_entry.hra_earned',
            'overtime_wages' => 'workforce_payroll_entry.overtime_wages',
            'gross_salary' => 'workforce_payroll_entry.gross_salary',
            'pf_employee' => 'workforce_payroll_entry.pf_employee',
            'esi_employee' => 'workforce_payroll_entry.esi_employee',
            'advances' => 'workforce_payroll_entry.advances',
            'fines' => 'workforce_payroll_entry.fines',
            'total_deductions' => 'workforce_payroll_entry.total_deductions',
            'net_salary' => 'workforce_payroll_entry.net_salary',
            'total_days_worked' => 'workforce_payroll_entry.total_days_worked',
        ]
    ],

    'FORM_10' => [
        'table' => 'workforce_payroll_entry',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_payroll_entry.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_id' => 'workforce_payroll_entry.employee_id',
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'overtime_hours' => 'workforce_payroll_entry.overtime_hours',
            'overtime_wages' => 'workforce_payroll_entry.overtime_wages'
        ]
    ],

    'FORM_25' => [
        'table' => 'workforce_payroll_entry',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_payroll_entry.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_id' => 'workforce_payroll_entry.employee_id',
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'total_days_worked' => 'workforce_payroll_entry.total_days_worked'
        ]
    ],

    'FORM_12' => [
        'table' => 'workforce_employee',
        'date_field' => 'created_at',
        'branch_filter' => true,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_jan_31',
        'fields' => ['employee_code' => 'employee_code', 'name' => 'name', 'designation' => 'designation']
    ],

    'FORM_2' => [
        'table' => 'workforce_attendance',
        'date_field' => 'attendance_date',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'fields' => []
    ],

    'FORM_7' => [
        'table' => 'inspection_documents',
        'date_field' => 'inspection_date',
        'branch_filter' => false,
        'filing_frequency' => 'event_based',
        'due_rule' => 'same_day',
        'fields' => []
    ],

    'FORM_11' => [
        'table' => 'incident_documents',
        'date_field' => 'incident_date',
        'branch_filter' => false,
        'filing_frequency' => 'event_based',
        'due_rule' => 'same_day',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'incident_documents.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'incident_date' => 'incident_documents.incident_date',
            'incident_type' => 'incident_documents.incident_type',
            'description' => 'incident_documents.description'
        ]
    ],

    'FORM_17' => [
        'table' => 'workforce_employee',
        'date_field' => 'created_at',
        'branch_filter' => true,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_jan_31',
        'fields' => []
    ],

    'FORM_18' => [
        'table' => 'workforce_employee',
        'date_field' => 'created_at',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [],
        'fields' => [
            'employee_code' => 'employee_code',
            'employee_name' => 'name',
            'designation' => 'designation',
            'date_of_joining' => 'date_of_joining',
        ],
    ],


    'FORM_26' => [
        'table' => 'incident_documents',
        'date_field' => 'incident_date',
        'branch_filter' => false,
        'filing_frequency' => 'event_based',
        'due_rule' => 'same_day',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'incident_documents.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'incident_date' => 'incident_documents.incident_date',
            'incident_type' => 'incident_documents.incident_type',
            'description' => 'incident_documents.description'
        ]
    ],

    'FORM_26A' => [
        'table' => 'incident_documents',
        'date_field' => 'incident_date',
        'branch_filter' => false,
        'filing_frequency' => 'event_based',
        'due_rule' => 'same_day',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'incident_documents.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'incident_date' => 'incident_documents.incident_date',
            'incident_type' => 'incident_documents.incident_type',
            'description' => 'incident_documents.description'
        ]
    ],

    // CLRA FORMS (13)
    'FORM_XII' => [
        'table' => 'contractor_master',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'fields' => ['company_name' => 'company_name', 'license_number' => 'license_number']
    ],

    'CLRA_LICENSE' => [
        'table' => 'contractor_compliance',
        'date_field' => 'created_at',
        'branch_filter' => true,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_jan_31',
        'joins' => [
            ['table' => 'contractor_master', 'first' => 'contractor_compliance.contractor_id', 'operator' => '=', 'second' => 'contractor_master.id']
        ],
        'fields' => []
    ],

    'FORM_XIII' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'contractor_master', 'first' => 'contract_labour_deployment.contractor_id', 'operator' => '=', 'second' => 'contractor_master.id'],
            ['table' => 'workforce_employee', 'first' => 'contract_labour_deployment.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'worker_name' => 'workforce_employee.name',
            'contractor_name' => 'contractor_master.company_name',
            'deployment_start' => 'contract_labour_deployment.deployment_start',
            'deployment_end' => 'contract_labour_deployment.deployment_end',
            'wage_rate' => 'contract_labour_deployment.wage_rate',
            'work_order' => 'contract_labour_deployment.work_order_number',
        ]
    ],

    'FORM_XVI' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'contract_labour_deployment.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id'],
            ['table' => 'contractor_master', 'first' => 'contract_labour_deployment.contractor_id', 'operator' => '=', 'second' => 'contractor_master.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'contractor_name' => 'contractor_master.company_name',
            'wage_rate' => 'contract_labour_deployment.wage_rate'
        ]
    ],

    'FORM_XVII' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'contract_labour_deployment.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id'],
            ['table' => 'contractor_master', 'first' => 'contract_labour_deployment.contractor_id', 'operator' => '=', 'second' => 'contractor_master.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'contractor_name' => 'contractor_master.company_name',
            'wage_rate' => 'contract_labour_deployment.wage_rate'
        ]
    ],

    'FORM_XIX' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'contract_labour_deployment.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id'],
            ['table' => 'contractor_master', 'first' => 'contract_labour_deployment.contractor_id', 'operator' => '=', 'second' => 'contractor_master.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'contractor_name' => 'contractor_master.company_name'
        ]
    ],

    'FORM_XIV' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'fields' => []
    ],

    'FORM_XX' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'fields' => []
    ],

    'FORM_XXI' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'fields' => []
    ],

    'FORM_XXII' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'fields' => []
    ],

    'FORM_XXIII' => [
        'table' => 'contract_labour_deployment',
        'date_field' => 'deployment_start',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'contract_labour_deployment.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id'],
            ['table' => 'contractor_master', 'first' => 'contract_labour_deployment.contractor_id', 'operator' => '=', 'second' => 'contractor_master.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'contractor_name' => 'contractor_master.company_name',
            'overtime_hours' => 'contract_labour_deployment.overtime_hours',
            'overtime_wages' => 'contract_labour_deployment.overtime_wages'
        ]
    ],

    'FORM_XXIV' => [
        'table' => 'clra_returns',
        'date_field' => 'period_from',
        'branch_filter' => false,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_feb_15',
        'fields' => []
    ],

    'FORM_XXV' => [
        'table' => 'clra_returns',
        'date_field' => 'period_from',
        'branch_filter' => false,
        'filing_frequency' => 'half_yearly',
        'due_rule' => 'next_half_year_30',
        'fields' => []
    ],

    'CLRA_RETURN' => [
        'table' => 'clra_returns',
        'date_field' => 'period_from',
        'branch_filter' => false,
        'filing_frequency' => 'half_yearly',
        'due_rule' => 'next_half_year_30',
        'fields' => []
    ],

    // SHOPS FORMS (7)
    'SHOPS_FORM_12' => [
        'table' => 'workforce_payroll_entry',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_payroll_entry.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'basic_earned' => 'workforce_payroll_entry.basic_earned',
            'gross_salary' => 'workforce_payroll_entry.gross_salary',
            'net_salary' => 'workforce_payroll_entry.net_salary',
            'advances' => 'workforce_payroll_entry.advances'
        ]
    ],

    'SHOPS_FORM_1' => [
        'table' => 'workforce_employee',
        'date_field' => 'created_at',
        'branch_filter' => true,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_jan_31',
        'fields' => []
    ],

    'SHOPS_FINES' => [
        'table' => 'workforce_payroll_entry',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_payroll_entry.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_id' => 'workforce_payroll_entry.employee_id',
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'fines' => 'workforce_payroll_entry.fines'
        ]
    ],

    'SHOPS_FORM_C' => [
        'table' => 'bonus_records',
        'date_field' => 'payment_date',
        'branch_filter' => false,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_jan_31',
        'fields' => ['bonus_amount' => 'bonus_amount']
    ],

    'SHOPS_UNPAID' => [
        'table' => 'bonus_records',
        'date_field' => 'payment_date',
        'branch_filter' => false,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_jan_31',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'bonus_records.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'bonus_amount' => 'bonus_records.bonus_amount'
        ]
    ],

    'SHOPS_FORM_VI' => [
        'table' => 'workforce_attendance',
        'date_field' => 'attendance_date',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'fields' => []
    ],

    // SOCIAL SECURITY FORMS (2)
    'ESI_FORM_12' => [
        'table' => 'incident_documents',
        'date_field' => 'incident_date',
        'branch_filter' => false,
        'filing_frequency' => 'event_based',
        'due_rule' => 'same_day',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'incident_documents.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_name' => 'workforce_employee.name',
            'esi_number' => 'workforce_employee.esi_number',
            'incident_date' => 'incident_documents.incident_date',
            'incident_type' => 'incident_documents.incident_type',
            'location' => 'incident_documents.location',
            'description' => 'incident_documents.description',
        ]
    ],

    'EPF_INSPECTION' => [
        'table' => 'inspection_documents',
        'date_field' => 'inspection_date',
        'branch_filter' => false,
        'filing_frequency' => 'event_based',
        'due_rule' => 'same_day',
        'fields' => [
            'inspection_date' => 'inspection_date',
            'authority' => 'inspecting_authority',
            'reference' => 'reference_number',
            'remarks' => 'remarks',
        ]
    ],

    // LABOUR WELFARE REGISTERS (5)
    'FORM_A' => [
        'table' => 'workforce_employee',
            'generator_class' => \App\Services\Compliance\Generators\FormAGenerator::class,
        'date_field' => 'created_at',
        'branch_filter' => true,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [],
        'fields' => [
            'employee_code' => 'employee_code',
            'employee_name' => 'name',
            'designation' => 'designation',
            'date_of_joining' => 'date_of_joining',
        ]
    ],

    'FORM_C' => [
        'table' => 'workforce_payroll_entry',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_payroll_entry.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'advances' => 'workforce_payroll_entry.advances',
            'fines' => 'workforce_payroll_entry.fines',
            'total_deductions' => 'workforce_payroll_entry.total_deductions',
        ]
    ],

    'FORM_D' => [
        'table' => 'workforce_attendance',
        'date_field' => 'attendance_date',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_attendance.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'attendance_date' => 'workforce_attendance.attendance_date',
            'status' => 'workforce_attendance.status',
        ]
    ],

    'FORM_D_ER' => [
        'table' => 'workforce_payroll_entry',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'monthly',
        'due_rule' => 'next_month_10',
        'joins' => [
            ['table' => 'workforce_employee', 'first' => 'workforce_payroll_entry.employee_id', 'operator' => '=', 'second' => 'workforce_employee.id']
        ],
        'fields' => [
            'employee_code' => 'workforce_employee.employee_code',
            'employee_name' => 'workforce_employee.name',
            'designation' => 'workforce_employee.designation',
            'basic_earned' => 'workforce_payroll_entry.basic_earned',
            'gross_salary' => 'workforce_payroll_entry.gross_salary',
        ]
    ],


    'CONTRACTOR_MASTER' => [
        'table' => 'contractor_master',
        'date_field' => 'created_at',
        'branch_filter' => false,
        'filing_frequency' => 'annual',
        'due_rule' => 'next_year_jan_31',
        'fields' => [
            'company_name' => 'company_name',
            'license_number' => 'license_number',
            'valid_from' => 'valid_from',
            'valid_to' => 'valid_to',
        ]
    ],
];
