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
        Schema::create('aics_clean_list', function (Blueprint $table) {
            $table->id();
            $table->string('control_number')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('extension_name')->nullable();
            $table->string('birth_day');
            $table->string('birth_month');
            $table->string('birth_year');
            $table->string('sex')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('occupation')->nullable();
            $table->string('monthy_salary')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->string('type_of_assistance')->nullable();
            $table->string('province')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->string('purok')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('amount')->nullable();
            $table->string('charging')->nullable();
            $table->string('file_source')->nullable();
            $table->string('payout_site')->nullable();
            $table->string('requesting_partner')->nullable();
            $table->string('poo_rdv_focal')->nullable();
            $table->string('sdo')->nullable();
            $table->string('check_number')->nullable();
            $table->string('check_date_issued')->nullable();
            $table->string('payout_mode')->nullable();
            $table->string('force_entry')->nullable();
            $table->string('date_processed')->nullable();
            $table->timestamps()->useCurrent()->useCurrentOnUpdate();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aics_requests');
    }
};
