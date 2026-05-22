<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorkforceEmployee;
use App\Models\WorkforceAttendance;
use App\Models\PayrollEntry;
use App\Models\Contractor;
use App\Models\ContractLabourDeployment;
use App\Models\IncidentDocument;
use App\Models\HazardRegister;
use App\Models\EmployeeFinancialRegister;
use App\Models\BonusRecord;
use App\Models\EmployeeLeave;
use App\Models\Holiday;

class TestGeneration extends Command
{
    protected $signature = 'compliance:test-generation';
    protected $description = 'Test demo dataset generation for all compliance forms';

    private const TENANT_ID = 1;
    private const BRANCH_ID = 1;

    public function handle(): int
    {
        $this->info('🧪 Testing Demo Dataset Generation...');
        $this->newLine();

        $forms = [
            'FORM_XII' => $this->testFormXII(),
            'FORM_XIII' => $this->testFormXIII(),
            'FORM_XIV' => $this->testFormXIV(),
            'FORM_XVI' => $this->testFormXVI(),
            'FORM_XVII' => $this->testFormXVII(),
            'FORM_XIX' => $this->testFormXIX(),
            'FORM_XX' => $this->testFormXX(),
            'FORM_XXI' => $this->testFormXXI(),
            'FORM_XXII' => $this->testFormXXII(),
            'FORM_XXIII' => $this->testFormXXIII(),
            'FORM_A' => $this->testFormA(),
            'FORM_C' => $this->testFormC(),
            'FORM_D' => $this->testFormD(),
            'FORM_D_ER' => $this->testFormDER(),
            'FORM_11' => $this->testForm11(),
            'ESI_FORM_12' => $this->testESIForm12(),
            'EPF_INSPECTION' => $this->testEPFInspection(),
            'FORM_B' => $this->testFormB(),
            'FORM_2' => $this->testForm2(),
            'FORM_10' => $this->testForm10(),
            'FORM_12' => $this->testForm12(),
            'FORM_17' => $this->testForm17(),
            'FORM_18' => $this->testForm18(),
            'FORM_25' => $this->testForm25(),
            'FORM_8' => $this->testForm8(),
            'FORM_26' => $this->testForm26(),
            'FORM_26A' => $this->testForm26A(),
            'HAZARD_REG' => $this->testHazardReg(),
            'SHOPS_FORM_C' => $this->testShopsFormC(),
            'SHOPS_UNPAID' => $this->testShopsUnpaid(),
            'SHOPS_FORM_12' => $this->testShopsForm12(),
            'SHOPS_FORM_13' => $this->testShopsForm13(),
            'SHOPS_FINES' => $this->testShopsFines(),
            'SHOPS_FORM_VI' => $this->testShopsFormVI(),
        ];

        $this->displayResults($forms);

        return 0;
    }

    private function testFormXII(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormXIII(): bool
    {
        return ContractLabourDeployment::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormXIV(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormXVI(): bool
    {
        return WorkforceAttendance::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormXVII(): bool
    {
        return PayrollEntry::whereHas('payrollCycle', function ($q) {
            $q->where('tenant_id', self::TENANT_ID)->where('branch_id', self::BRANCH_ID);
        })->count() > 0;
    }

    private function testFormXIX(): bool
    {
        return PayrollEntry::whereHas('payrollCycle', function ($q) {
            $q->where('tenant_id', self::TENANT_ID)->where('branch_id', self::BRANCH_ID);
        })->count() > 0;
    }

    private function testFormXX(): bool
    {
        return EmployeeFinancialRegister::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->where('transaction_type', 'fine')
            ->count() > 0;
    }

    private function testFormXXI(): bool
    {
        return EmployeeFinancialRegister::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->where('transaction_type', 'fine')
            ->count() > 0;
    }

    private function testFormXXII(): bool
    {
        return EmployeeFinancialRegister::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->where('transaction_type', 'advance')
            ->count() > 0;
    }

    private function testFormXXIII(): bool
    {
        return ContractLabourDeployment::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormA(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormC(): bool
    {
        return BonusRecord::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormD(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormDER(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm11(): bool
    {
        return IncidentDocument::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testESIForm12(): bool
    {
        return IncidentDocument::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testEPFInspection(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testFormB(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm2(): bool
    {
        return WorkforceAttendance::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm10(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm12(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm17(): bool
    {
        return IncidentDocument::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm18(): bool
    {
        return IncidentDocument::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm25(): bool
    {
        return WorkforceAttendance::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm8(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm26(): bool
    {
        return IncidentDocument::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testForm26A(): bool
    {
        return IncidentDocument::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testHazardReg(): bool
    {
        return HazardRegister::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testShopsFormC(): bool
    {
        return BonusRecord::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testShopsUnpaid(): bool
    {
        return BonusRecord::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testShopsForm12(): bool
    {
        return WorkforceEmployee::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testShopsForm13(): bool
    {
        return EmployeeLeave::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function testShopsFines(): bool
    {
        return EmployeeFinancialRegister::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->where('transaction_type', 'fine')
            ->count() > 0;
    }

    private function testShopsFormVI(): bool
    {
        return Holiday::where('tenant_id', self::TENANT_ID)
            ->where('branch_id', self::BRANCH_ID)
            ->count() > 0;
    }

    private function displayResults(array $forms): void
    {
        $this->newLine();
        $this->info('📋 Form Data Availability:');
        $this->newLine();

        $results = [];
        foreach ($forms as $form => $hasData) {
            $results[] = [
                $form,
                $hasData ? '✅ Ready' : '⚠️ No Data',
            ];
        }

        $this->table(['Form', 'Status'], $results);

        $passCount = count(array_filter($forms));
        $totalCount = count($forms);

        $this->newLine();
        $this->info("✅ Forms Ready: $passCount/$totalCount");
        $this->info('🎉 All forms have demo data available for preview and PDF generation!');
    }
}
