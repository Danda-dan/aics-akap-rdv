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
        Schema::create('ect_requests', function (Blueprint $table) {
            $table->id();
            $table->string('stakeholder');
            $table->string('focal_person');
            $table->string('file_name');
            $table->string('date_received');
            $table->string('raw_list');
            $table->string('possible_duplicates');
            $table->string('invalid_records');
            $table->string('served_individuals');
            $table->string('total_valid');
            $table->string('contact_person');
            $table->string('contact_number');
            $table->string('contact_email');
            $table->string('prepared_by');
            $table->string('division_chief');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ect_requests');
    }
};
