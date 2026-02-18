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
        Schema::create('aics_no_show_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('control_number', 50)->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('extension_name')->nullable();
            $table->string('birth_day');
            $table->string('birth_month');
            $table->string('birth_year');
            $table->string('sex', 50)->nullable();
            $table->string('civil_status', 50)->nullable();
            $table->string('occupation', 50)->nullable();
            $table->string('monthly_salary', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('subcategory', 50)->nullable();
            $table->string('type_of_assistance', 50)->nullable();
            $table->string('province')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->string('purok')->nullable();
            $table->string('contact_number', 50)->nullable();
            $table->string('amount', 50)->nullable();
            $table->string('charging', 50)->nullable();
            $table->string('file_source')->nullable();
            $table->string('payout_site', 50)->nullable();
            $table->string('requesting_partner', 50)->nullable();
            $table->string('poo_rdv_focal', 50)->nullable();
            $table->string('sdo', 50)->nullable();
            $table->string('payout_mode', 50)->nullable();
            $table->string('force_entry', 50)->nullable();
            $table->string('check_number', 50)->nullable();
            $table->string('check_date_issued', 50)->nullable();
            $table->string('date_processed', 50)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aics_no_show_list');
    }
};
