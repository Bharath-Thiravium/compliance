<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }
};
