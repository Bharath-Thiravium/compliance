<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ComplianceGenerateFormService extends Command
{
    protected $signature = 'compliance:generate-service {form}';

    protected $description = 'Generate Form Service from Blade template';

    public function handle()
    {
        $form = strtoupper($this->argument('form'));

        $bladePath = resource_path("views/compliance/forms/" . strtolower($form) . ".blade.php");

        if (!File::exists($bladePath)) {
            $this->error("Blade template not found: $bladePath");
            return;
        }

        $blade = File::get($bladePath);

        preg_match_all("/data_get\\(\\\$row,'(.*?)'/", $blade, $matches);

        $columns = array_unique($matches[1]);

        $this->info("Detected Columns:");

        foreach ($columns as $col) {
            $this->line("- $col");
        }

        $select = [];

        foreach ($columns as $col) {
            $select[] = "DB::raw(\"'' as $col\")";
        }

        $selectCode = implode(",\n                ", $select);

        $className = str_replace('_', '', ucwords(strtolower($form), '_')) . "Service";

        $service = "<?php

namespace App\Services\Compliance\Forms;

use Illuminate\Support\Facades\DB;

class {$className} extends BaseFormService
{
    public function generate(int \$tenantId, int \$branchId, int \$month, int \$year): array
    {
        \$this->tenantId = \$tenantId;
        \$this->branchId = \$branchId;
        \$this->month = \$month;
        \$this->year = \$year;

        \$rows = DB::table('workforce_employee as e')
            ->where('e.tenant_id', \$tenantId)
            ->where('e.branch_id', \$branchId)
            ->select([
                {$selectCode}
            ])
            ->get()
            ->map(fn(\$row) => (array)\$row)
            ->toArray();

        if (empty(\$rows)) {
            return \$this->nilResponse();
        }

        return \$this->buildResponse(\$rows);
    }
}
";

        $servicePath = app_path("Services/Compliance/Forms/{$className}.php");

        File::put($servicePath, $service);

        $this->info("Service generated: $className");
    }
}
