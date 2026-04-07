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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active');
            $table->text('comment')->default('');
            $table->integer('last_file_number');
            $table->string('measurement_interval')->default('');
            $table->string('movement_magnitude')->default('');
            $table->binary('image')->nullable();
            $table->string('image_mime_type', 50)->nullable();
            // former fk
            $table->string('client');
            $table->string('clerk');
            $table->string('type');
            $table->string('municipality');
            // FK constraint added after measurements table is created
            $table->foreignId('reference_measurement_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
