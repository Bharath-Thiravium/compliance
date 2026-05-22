<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_form_sources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('form_id')->index();
            $table->string('source_table');
            $table->enum('source_type', ['Payroll', 'Attendance', 'CLRA', 'Upload']);
            $table->timestamps();

            $table->foreign('form_id')->references('id')->on('compliance_forms_master')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_form_sources');
    }
};
