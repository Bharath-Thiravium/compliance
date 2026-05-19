<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateComplianceDemoDataset extends Command
{
    protected $signature = 'compliance:generate-demo-dataset 
                            {--tenant_id=1 : Tenant ID}
                            {--branch_id=1 : Branch ID}
                            {--month=1 : Month (1-12)}
                            {--year=2024 : Year}';

    protected $description = 'Generate demo data for empty compliance datasets (fines, advances)';

    public function handle(): int
    {
        $tenantId = (int) $this->option('tenant_id');
        $branchId = (int) $this->option('branch_id');
        $month = (int) $this->option('month');
        $year = (int) $this->option('year');

        $this->info("Generating demo compliance data for Tenant {$tenantId}, Branch {$branchId}");
        $this->newLine();

        try {
            DB::transaction(function () use ($tenantId, $branchId, $month, $year) {
                $employees = $this->getEmployees($tenantId, $branchId);
                
                if (empty($employees)) {
                    $this->warn("No employees found for tenant {$tenantId}, branch {$branchId}");
                    return;
                }

                $finesCount = $this->seedFines($tenantId, $branchId, $employees, $month, $year);
                $this->info("✓ Created {$finesCount} fines records");

                $advancesCount = $this->seedAdvances($tenantId, $branchId, $employees, $month, $year);
                $this->info("✓ Created {$advancesCount} advances records");

                $this->newLine();
                $this->info("✅ Demo dataset generated successfully");
            });

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed: " . $e->getMessage());
            return 1;
        }
    }

    private function getEmployees(int $tenantId, int $branchId): array
    {
        return DB::table('workforce_employee')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
    }

    private function seedFines(int $tenantId, int $branchId, array $employees, int $month, int $year): int
    {
        $reasons = [
            'Unauthorized absence',
            'Late arrival',
            'Insubordination',
            'Breach of safety protocol',
            'Damage to equipment',
            'Poor work quality',
            'Violation of workplace rules',
        ];

        $count = 0;
        $periodStart = Carbon::create($year, $month, 1);
        $periodEnd = $periodStart->copy()->endOfMonth();

        for ($i = 0; $i < min(15, count($employees)); $i++) {
            $fineDate = $periodStart->copy()->addDays(rand(0, $periodEnd->diffInDays($periodStart)));
            
            DB::table('workforce_fines')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $employees[$i],
                'fine_date' => $fineDate,
                'amount' => rand(100, 500),
                'reason' => $reasons[array_rand($reasons)],
                'remarks' => 'Fine imposed as per company policy',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    private function seedAdvances(int $tenantId, int $branchId, array $employees, int $month, int $year): int
    {
        $reasons = [
            'Personal emergency',
            'Medical expenses',
            'Family event',
            'Home repair',
            'Education fees',
            'Travel expenses',
        ];

        $count = 0;
        $periodStart = Carbon::create($year, $month, 1);
        $periodEnd = $periodStart->copy()->endOfMonth();

        for ($i = 0; $i < min(12, count($employees)); $i++) {
            $advanceDate = $periodStart->copy()->addDays(rand(0, $periodEnd->diffInDays($periodStart)));
            
            DB::table('workforce_advances')->insert([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'employee_id' => $employees[$i],
                'advance_date' => $advanceDate,
                'advance_amount' => rand(2000, 10000),
                'reason' => $reasons[array_rand($reasons)],
                'remarks' => 'Advance approved and disbursed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }
}
