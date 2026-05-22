<?php

namespace App\Services\Compliance;

use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceReminder;
use App\Models\ContractorCompliance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComplianceReminderService
{
    public function generateMonthlyReminders(): void
    {
        $forms = ComplianceFormsMaster::where('frequency', 'Monthly')
            ->where('is_active', true)
            ->get();

        foreach ($forms as $form) {
            $dueDate = Carbon::now()->addMonth()->endOfMonth()->addDays(10);

            ComplianceReminder::firstOrCreate([
                'tenant_id' => auth()->user()->tenant_id,
                'form_id' => $form->id,
                'reminder_type' => 'Monthly',
                'due_date' => $dueDate,
            ], [
                'status' => 'Pending',
            ]);
        }
    }

    public function generateAnnualReminders(): void
    {
        $forms = ComplianceFormsMaster::where('frequency', 'Annual')
            ->where('is_active', true)
            ->get();

        foreach ($forms as $form) {
            $dueDate = Carbon::now()->addYear()->startOfYear()->addDays(30);

            ComplianceReminder::firstOrCreate([
                'tenant_id' => auth()->user()->tenant_id,
                'form_id' => $form->id,
                'reminder_type' => 'Annual',
                'due_date' => $dueDate,
            ], [
                'status' => 'Pending',
            ]);
        }
    }

    public function checkExpiringContractorLicense(): void
    {
        $expiringLicenses = ContractorCompliance::where('license_valid_to', '<=', Carbon::now()->addDays(30))
            ->where('license_valid_to', '>=', Carbon::now())
            ->get();

        foreach ($expiringLicenses as $license) {
            $form = ComplianceFormsMaster::where('form_code', 'CLRA_LICENSE_RENEWAL')
                ->where('is_active', true)
                ->first();

            if ($form) {
                ComplianceReminder::firstOrCreate([
                    'tenant_id' => $license->contractor->tenant_id,
                    'form_id' => $form->id,
                    'reminder_type' => 'Expiry',
                    'due_date' => $license->license_valid_to,
                ], [
                    'status' => 'Pending',
                ]);
            }
        }
    }

    public function sendPendingReminders(): int
    {
        $reminders = ComplianceReminder::where('status', 'Pending')
            ->where('due_date', '<=', Carbon::now()->addDays(7))
            ->whereNull('reminder_sent_at')
            ->get();

        $sent = 0;
        foreach ($reminders as $reminder) {
            // Placeholder for actual notification logic
            $reminder->update([
                'status' => 'Sent',
                'reminder_sent_at' => now(),
            ]);
            $sent++;
        }

        return $sent;
    }
}
