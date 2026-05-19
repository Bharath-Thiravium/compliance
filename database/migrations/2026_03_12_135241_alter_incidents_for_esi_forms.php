<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {

            $table->foreignId('employee_id')->nullable();

            $table->date('notice_date')->nullable();
            $table->time('notice_time')->nullable();

            $table->time('incident_time')->nullable();

            $table->string('location')->nullable();

            $table->text('cause')->nullable();
            $table->string('injury_type')->nullable();

            $table->text('activity')->nullable();

            $table->string('first_aid_by')->nullable();

            $table->text('witness')->nullable();

            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
