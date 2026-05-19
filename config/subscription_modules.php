<?php

return [
    'MINIMAL' => [
        'allowed_forms' => [
            'FORM_12', 'FORM_17', 'SHOPS_FORM_1', 'CONTRACTOR_MASTER',
        ],
        'features' => [
            'manual_upload' => true,
            'limited_generation' => true,
            'analysis_report' => true,
            'auto_generation' => false,
            'inspection_pack' => false,
            'digital_signature' => false,
            'bulk_automation' => false,
        ],
    ],
    
    'FULL' => [
        'allowed_forms' => 'all',
        'features' => [
            'manual_upload' => true,
            'limited_generation' => true,
            'analysis_report' => true,
            'auto_generation' => true,
            'inspection_pack' => true,
            'digital_signature' => true,
            'bulk_automation' => true,
        ],
    ],
];
