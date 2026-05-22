<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clra_returns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->enum('return_type', ['half_yearly', 'annual'])->index();
            $table->date('period_from');
            $table->date('period_to');
            $table->integer('total_workers');
            $table->decimal('total_wages', 14, 2);
            $table->decimal('total_ot', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clra_returns');
    }
};
