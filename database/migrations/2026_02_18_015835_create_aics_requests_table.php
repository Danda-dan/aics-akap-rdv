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
        Schema::create('aics_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payout_site', 50)->nullable();
            $table->string('payout_mode', 50)->nullable();
            $table->string('requesting_partner')->nullable();
            $table->string('poo_rdv_focal')->nullable();
            $table->string('file_name')->nullable();
            $table->date('date_received')->nullable();
            $table->string('raw_list', 50)->nullable();
            $table->string('possible_duplicates')->nullable();
            $table->string('invalid_records')->nullable();
            $table->string('served_individuals')->nullable();
            $table->string('total_valid')->nullable();
            $table->string('sdo')->nullable();
            $table->string('check_number')->nullable();
            $table->date('check_date_issued')->nullable();
            $table->string('prepared_by')->nullable();
            $table->string('division_chief')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
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
