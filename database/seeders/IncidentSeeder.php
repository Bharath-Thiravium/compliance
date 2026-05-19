<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $incidents = [];
        $incidentId = 1;

        $branches = DB::table('branches')->get();

        foreach ($branches as $branch) {
            // Create 3-5 incidents per branch
            $incidentCount = rand(3, 5);

            for ($i = 0; $i < $incidentCount; $i++) {
                $incidents[] = [
                    'id' => $incidentId++,
                    'tenant_id' => $branch->tenant_id,
                    'branch_id' => $branch->id,
                    'incident_date' => Carbon::now()->subDays(rand(1, 90))->toDateString(),
                    'description' => 'Incident ' . $i . ' at ' . $branch->branch_name,
                    'severity' => ['Low', 'Medium', 'High', 'Critical'][rand(0, 3)],
                    'status' => ['Open', 'In Progress', 'Resolved', 'Closed'][rand(0, 3)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('incidents')->insert($incidents);
    }
}
