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
        Schema::create('aics_served_database', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('control_number', 50);
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('extension_name', 50)->nullable();
            $table->string('birth_day', 50);
            $table->string('birth_month', 50);
            $table->string('birth_year', 50);
            $table->string('province', 50)->nullable();
            $table->string('city_municipality', 50)->nullable();
            $table->string('date_last_served', 50)->nullable();
            $table->string('last_served_location', 50)->nullable();
            $table->string('program', 50)->nullable();
            $table->string('event_type', 50)->nullable();
            $table->string('partners', 150)->nullable();
            $table->string('charging', 50)->nullable();
            $table->string('sdo_incharge', 50)->nullable();
            $table->string('other_remarks', 50)->nullable();
            $table->string('file_source', 50)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aics_served_database');
    }
};
