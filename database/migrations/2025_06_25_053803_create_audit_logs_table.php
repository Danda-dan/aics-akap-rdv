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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');             // Table being audited
            $table->string('action');                 // INSERT, UPDATE, DELETE
            $table->unsignedBigInteger('record_id')->nullable(); // ID of the affected record
            $table->text('old_data')->nullable();     // JSON or text of previous data
            $table->text('new_data')->nullable();     // JSON or text of new data
            $table->timestamp('changed_at')->useCurrent(); // Timestamp of the change
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
