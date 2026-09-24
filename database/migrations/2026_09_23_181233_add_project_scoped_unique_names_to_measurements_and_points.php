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
        Schema::table('measurements', function (Blueprint $table) {
            $table->unique(['project_id', 'name'], 'measurements_project_id_name_unique');
        });

        Schema::table('points', function (Blueprint $table) {
            $table->unique(['project_id', 'name'], 'points_project_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropUnique('measurements_project_id_name_unique');
        });

        Schema::table('points', function (Blueprint $table) {
            $table->dropUnique('points_project_id_name_unique');
        });
    }
};
