<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_manual_master', function (Blueprint $table) {
            $table->id();
            $table->string('compliance_name');
            $table->string('act_name');
            $table->enum('frequency', ['monthly', 'quarterly', 'half_yearly', 'annual', 'event']);
            $table->integer('due_month')->nullable();
            $table->boolean('requires_document')->default(true);
            $table->boolean('is_event_based')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_manual_master');
    }
};
