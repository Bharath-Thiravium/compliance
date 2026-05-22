<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contractor_master')) {
            Schema::create('contractor_master', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->index();
                $table->string('company_type')->nullable();
                $table->string('company_name');
                $table->string('license_number')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->integer('max_worker_limit')->default(0);
                $table->string('company_address')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('email')->nullable();
                $table->string('pan_number')->nullable();
                $table->string('gst_number')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->index(['tenant_id', 'company_name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_master');
    }
};
