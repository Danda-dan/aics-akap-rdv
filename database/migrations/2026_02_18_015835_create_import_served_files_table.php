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
        Schema::create('import_served_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name', 50)->nullable();
            $table->integer('total_served')->nullable();
            $table->string('location', 50)->nullable();
            $table->string('imported_by', 50)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_served_files');
    }
};
