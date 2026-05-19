<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_reminders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('form_id')->index();
            $table->enum('reminder_type', ['Monthly', 'Annual', 'Expiry', 'Event']);
            $table->date('due_date')->index();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->enum('status', ['Pending', 'Sent', 'Skipped'])->default('Pending');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('compliance_forms_master')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_reminders');
    }
};
