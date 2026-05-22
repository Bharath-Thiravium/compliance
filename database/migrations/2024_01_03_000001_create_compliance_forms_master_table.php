<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_forms_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('form_code')->unique()->index();
            $table->string('form_name');
            $table->enum('act_type', ['Factories', 'CLRA', 'Shops', 'EPF', 'ESI']);
            $table->enum('frequency', ['Monthly', 'Annual', 'HalfYearly', 'Event']);
            $table->enum('priority', ['High', 'Medium', 'Low'])->default('Medium');
            $table->boolean('auto_generate')->default(false);
            $table->boolean('upload_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_forms_master');
    }
};
