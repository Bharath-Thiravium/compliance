<?php

namespace App\Services\Compliance\FormApis;

use Illuminate\Support\Facades\DB;

/**
 * Form2ApiService - FORM 2: Notice of Periods of Work
 * 
 * Statutory Reference: Tamil Nadu Factories Rules, Rule 79
 * 
 * This form displays work periods for adult workers and children organized by:
 * - Worker categories: Men, Women, Children
 * - Groups: A-F (working days), G-I (partial working days)
 * - Relays: 1, 2, 3 (shift timings)
 * 
 * Data Model:
 * - NOT an employee register
 * - IS a shift/relay schedule notice
 * - Organized by group and relay, not by individual employee
 * 
 * API Output Structure:
 * [
 *     'factory_details' => [
 *         'factory_name' => string,
 *         'place' => string,
 *         'district' => string,
 *         'date_first_exhibited' => date,
 *     ],
 *     'working_days' => [
 *         'A' => ['relays' => [1 => '', 2 => '', 3 => ''], 'nature_of_work' => string],
 *         'B' => [...],
 *         'C' => [...],
 *         'D' => [...],
 *         'E' => [...],
 *         'F' => [...],
 *     ],
 *     'partial_working_days' => [
 *         'G' => ['relays' => [1 => '', 2 => '', 3 => ''], 'nature_of_work' => string],
 *         'H' => [...],
 *         'I' => [...],
 *     ],
 *     'meta' => [...],
 *     'tenant' => [...],
 *     'branch' => [...],
 *     'period' => string,
 * ]
 */
class Form2ApiService extends BaseFormApiService
{
    /**
     * Group definitions for statutory form
     * Maps group letters to nature of work
     * 
     * ASSUMPTION: These are predefined per factory type
     * Can be moved to statutory_manual_data table or config later
     */
    private const GROUP_DEFINITIONS = [
        // Working Days - Men
        'A' => 'Assembly and Testing',
        'B' => 'Quality Control',
        'C' => 'Packaging',
        // Working Days - Women
        'D' => 'Maintenance',
        'E' => 'Supervision',
        'F' => 'Administration',
        // Partial Working Days - Children
        'G' => 'Training',
        'H' => 'Apprenticeship',
        'I' => 'Internship',
    ];

    /**
     * Group categorization
     * Determines which section each group belongs to
     */
    private const GROUP_CATEGORIES = [
        'working_days' => ['A', 'B', 'C', 'D', 'E', 'F'],
        'partial_working_days' => ['G', 'H', 'I'],
    ];

    public function fetch(int $tenantId, int $branchId, int $month, int $year): array
    {
        $this->initializePeriod($month, $year);
        $this->validateTenantAndBranch($tenantId, $branchId);

        // Fetch factory details from branch
        $factoryDetails = $this->getFactoryDetails($branchId, $tenantId);

        // Build group/relay structure
        $workingDays = $this->buildGroupStructure(
            self::GROUP_CATEGORIES['working_days']
        );
        $partialWorkingDays = $this->buildGroupStructure(
            self::GROUP_CATEGORIES['partial_working_days']
        );

        return [
            'factory_details' => $factoryDetails,
            'working_days' => $workingDays,
            'partial_working_days' => $partialWorkingDays,
            'meta' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'month' => $month,
                'year' => $year,
            ],
            'tenant' => $this->getTenantDetails($tenantId),
            'branch' => $this->getBranchDetails($branchId, $tenantId),
            'period' => $this->formatPeriod(),
        ];
    }

    /**
     * Get factory details from branch
     * 
     * ASSUMPTION: Branch table contains factory information
     * - branch_name or unit_name → factory_name
     * - address → place (first part before comma)
     * - district → district
     * 
     * @return array Factory details
     */
    private function getFactoryDetails(int $branchId, int $tenantId): array
    {
        $branch = DB::table('branches')
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$branch) {
            return [
                'factory_name' => 'NIL',
                'place' => 'NIL',
                'district' => 'NIL',
                'date_first_exhibited' => $this->periodStart->format('Y-m-d'),
            ];
        }

        // Extract place from address (first part before comma)
        $place = $this->extractPlace($branch->address ?? '');

        return [
            'factory_name' => $branch->unit_name ?? $branch->branch_name ?? 'NIL',
            'place' => $place,
            'district' => $branch->district ?? 'NIL',
            'date_first_exhibited' => $this->periodStart->format('Y-m-d'),
        ];
    }

    /**
     * Extract place from full address
     * Takes first part before comma or full address if no comma
     * 
     * @param string $address Full address
     * @return string Place name
     */
    private function extractPlace(string $address): string
    {
        if (empty($address)) {
            return 'NIL';
        }

        $parts = explode(',', $address);
        return trim($parts[0]) ?: 'NIL';
    }

    /**
     * Build group structure with relays
     * 
     * Structure:
     * [
     *     'A' => [
     *         'relays' => [1 => '', 2 => '', 3 => ''],
     *         'nature_of_work' => 'Assembly and Testing',
     *     ],
     *     ...
     * ]
     * 
     * ASSUMPTION: Relay data is currently empty
     * Can be populated from shift_timings or workforce_payroll_entry later
     * 
     * @param array $groups Group letters to build
     * @return array Group structure
     */
    private function buildGroupStructure(array $groups): array
    {
        $structure = [];

        foreach ($groups as $group) {
            $structure[$group] = [
                'relays' => [
                    1 => '',  // Relay 1 - can be populated from shift data
                    2 => '',  // Relay 2 - can be populated from shift data
                    3 => '',  // Relay 3 - can be populated from shift data
                ],
                'nature_of_work' => self::GROUP_DEFINITIONS[$group] ?? 'NIL',
            ];
        }

        return $structure;
    }

    /**
     * Populate relay data from shift timings
     * 
     * FUTURE ENHANCEMENT:
     * This method can be called to populate relay cells with actual shift data
     * Currently not implemented as shift_timings table structure is not finalized
     * 
     * Expected source: shift_timings or workforce_payroll_entry
     * Expected data: Start time, end time, or worker count per relay
     * 
     * @param array $structure Group structure
     * @param int $branchId Branch ID
     * @return array Updated structure with relay data
     */
    private function populateRelayData(array $structure, int $branchId): array
    {
        // TODO: Implement when shift_timings table is available
        // $shifts = DB::table('shift_timings')
        //     ->where('branch_id', $branchId)
        //     ->get();
        //
        // foreach ($shifts as $shift) {
        //     $group = $shift->group_letter;
        //     $relay = $shift->relay_number;
        //     $structure[$group]['relays'][$relay] = $shift->timing_value;
        // }

        return $structure;
    }
}
