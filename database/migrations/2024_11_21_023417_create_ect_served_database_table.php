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
        Schema::create('ect_served_database', function (Blueprint $table) {
            $table->id();
            $table->integer('payroll_number');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('extension')->nullable();
            $table->string('barangay')->nullable();
            $table->string('lgu')->nullable();
            $table->string('sdo')->nullable();
            $table->date('date_paid')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ect_served_database');
    }
};
