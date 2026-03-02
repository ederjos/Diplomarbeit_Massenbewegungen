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
            $table->rawColumn('period', 'INTERVAL default \'6 months\'');
            $table->string('movement_magnitude')->default('');
            $table->binary('image')->nullable();
            $table->string('image_mime_type', 50)->nullable();
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('clerk_id')->constrained()->onDelete('restrict');
            $table->foreignId('type_id')->constrained()->onDelete('restrict');
            $table->foreignId('municipality_id')->constrained()->onDelete('restrict');
            // FK constraint added after measurements table is created
            $table->unsignedBigInteger('reference_measurement_id')->nullable();
            $table->timestamps();
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
