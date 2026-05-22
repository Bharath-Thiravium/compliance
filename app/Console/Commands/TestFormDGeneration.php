<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Compliance\FormGenerator\FormGeneratorFactory;

class TestFormDGeneration extends Command
{
    protected $signature = 'test:form-d {tenant_id=1} {branch_id=1} {month=12} {year=2024}';
    protected $description = 'Test Form D generation';

    public function handle()
    {
        $this->info('Testing Form D Generation...');
        
        try {
            $generator = FormGeneratorFactory::make('FormD');
            
            if (!$generator) {
                $this->error('Failed to create FormD generator');
                return 1;
            }
            
            $this->info('✓ Generator created');
            
            // Sample test data
            $testData = [
                'tenant' => [
                    'name' => 'M/S. MAK47',
                    'owner_name' => 'Test Owner'
                ],
                'branch' => [
                    'name' => 'Main Branch'
                ],
                'meta' => [
                    'month' => $this->argument('month'),
                    'year' => $this->argument('year')
                ],
                'records' => [
                    [
                        'employee_code' => 'EMP001',
                        'name' => 'ANBARASANI VALLIAPPAN',
                        'designation' => 'SECURITY GUARD',
                        'attendance_date' => '2024-12-01',
                        'status' => 'present'
                    ],
                    [
                        'employee_code' => 'EMP002',
                        'name' => 'CHANDRASOSE PALANIYANDI',
                        'designation' => 'SECURITY GUARD',
                        'attendance_date' => '2024-12-01',
                        'status' => 'present'
                    ],
                    [
                        'employee_code' => 'EMP003',
                        'name' => 'DANESAN SUNDARAJAN',
                        'designation' => 'SECURITY GUARD',
                        'attendance_date' => '2024-12-01',
                        'status' => 'present'
                    ]
                ]
            ];
            
            $this->info('✓ Test data prepared');
            
            $formData = $generator->generate($testData);
            
            $this->info('✓ Form data generated');
            $this->line('');
            $this->line('Form Data:');
            $this->line('Establishment: ' . ($formData['establishment_name'] ?? 'N/A'));
            $this->line('Owner: ' . ($formData['owner_name'] ?? 'N/A'));
            $this->line('Month: ' . ($formData['month_name'] ?? 'N/A'));
            $this->line('Year: ' . ($formData['year'] ?? 'N/A'));
            $this->line('Rows: ' . count($formData['rows'] ?? []));
            
            if (!empty($formData['rows'])) {
                $this->line('');
                $this->line('Sample Employee:');
                $firstRow = $formData['rows'][0];
                $this->line('  Name: ' . ($firstRow['employee_name'] ?? 'N/A'));
                $this->line('  Designation: ' . ($firstRow['designation'] ?? 'N/A'));
            }
            
            $this->info('');
            $this->info('✓ Form D generation successful!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
            return 1;
        }
    }
}
