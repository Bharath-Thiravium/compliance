<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create contract_labour_deployment table if it doesn't exist
        if (!Schema::hasTable('contract_labour_deployment')) {
            Schema::create('contract_labour_deployment', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->unsignedBigInteger('contractor_id')->nullable()->index();
                $table->unsignedBigInteger('contractor_compliance_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->decimal('wage_rate', 12, 2);
                $table->date('deployment_start');
                $table->date('deployment_end')->nullable();
                $table->string('work_order_number')->nullable();
                $table->date('work_order_date')->nullable();
                $table->string('status')->default('active');
                $table->decimal('overtime_hours', 8, 2)->default(0);
                $table->decimal('overtime_wages', 12, 2)->default(0);
                $table->date('deployment_date')->nullable();
                $table->integer('workmen_count')->default(0);
                $table->text('work_description')->nullable();
                $table->text('remarks')->nullable();
                $table->string('nature_of_work')->nullable();
                $table->string('work_location')->nullable();
                $table->string('termination_reason')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
                
                $table->index(['tenant_id', 'contractor_id']);
                $table->index(['contractor_id', 'employee_id']);
            });
        }

        // Create contractor_master table if it doesn't exist
        if (!Schema::hasTable('contractor_master')) {
            Schema::create('contractor_master', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('company_type')->nullable();
                $table->string('company_name');
                $table->string('contractor_code')->nullable();
                $table->string('contractor_name')->nullable();
                $table->string('license_number')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->integer('max_worker_limit')->default(0);
                $table->string('company_address')->nullable();
                $table->text('address')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('pan_number')->nullable();
                $table->string('gst_number')->nullable();
                $table->string('license_no')->nullable();
                $table->date('license_expiry')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['tenant_id', 'company_name']);
            });
        }

        // Create contractor_compliance table if it doesn't exist
        if (!Schema::hasTable('contractor_compliance')) {
            Schema::create('contractor_compliance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contractor_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('clra_license_number')->nullable();
                $table->date('license_valid_from')->nullable();
                $table->date('license_valid_to')->nullable();
                $table->integer('max_worker_limit')->default(0);
                $table->string('pf_code')->nullable();
                $table->string('esi_code')->nullable();
                $table->string('labour_registration_number')->nullable();
                $table->date('last_return_filed')->nullable();
                $table->boolean('is_compliant')->default(true);
                $table->text('compliance_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('contractor_id')->references('id')->on('contractor_master')->onDelete('cascade');
                $table->index(['contractor_id', 'branch_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_compliance');
        Schema::dropIfExists('contract_labour_deployment');
        Schema::dropIfExists('contractor_master');
    }
};
