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
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['can_add', 'can_edit']);
            $table->boolean('manage_users')->default(false);
            $table->boolean('manage_projects')->default(false);
            $table->boolean('manage_measurements')->default(false);
            $table->boolean('manage_comments')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('can_add')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->dropColumn([
                'manage_users',
                'manage_projects',
                'manage_measurements',
                'manage_comments',
            ]);
        });
    }
};
