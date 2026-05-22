<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->date('incident_date');
            $table->string('description')->nullable();
            $table->string('severity')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['incident_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
