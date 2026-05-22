<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('form_master_id')->constrained('compliance_forms_master')->onDelete('cascade');
            $table->tinyInteger('period_month');
            $table->year('period_year');
            $table->date('due_date');
            $table->enum('status', ['Pending', 'Generated', 'Filed', 'Overdue'])->default('Pending');
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'form_master_id', 'period_month', 'period_year'], 'timeline_unique');
            $table->index(['tenant_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_timelines');
    }
};
