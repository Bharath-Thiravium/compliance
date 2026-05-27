<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workforce_employee')) {
            return;
        }

        Schema::table('workforce_employee', function (Blueprint $table) {
            if ($this->indexExists('workforce_employee_employee_code_unique')) {
                $table->dropUnique('workforce_employee_employee_code_unique');
            }

            if (! $this->indexExists('workforce_employee_tenant_employee_code_unique')) {
                $table->unique(['tenant_id', 'employee_code'], 'workforce_employee_tenant_employee_code_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('workforce_employee')) {
            return;
        }

        Schema::table('workforce_employee', function (Blueprint $table) {
            if ($this->indexExists('workforce_employee_tenant_employee_code_unique')) {
                $table->dropUnique('workforce_employee_tenant_employee_code_unique');
            }

            if (! $this->indexExists('workforce_employee_employee_code_unique')) {
                $table->unique('employee_code', 'workforce_employee_employee_code_unique');
            }
        });
    }

    private function indexExists(string $indexName): bool
    {
        return collect(Schema::getIndexes('workforce_employee'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $indexName);
    }
};
