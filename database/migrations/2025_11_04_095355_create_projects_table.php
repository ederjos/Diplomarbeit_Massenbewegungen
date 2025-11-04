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
            $table->text('comment');
            $table->integer('last_file_number');
            $table->rawColumn('period', "INTERVAL default '2 months 14 days'"); // for pgsql native datatype
            
            // use automatically id for foreign key (table name is retrieved from local column name)
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->foreignId('clerk_id')->constrained()->onDelete('restrict');
            $table->foreignId('type_id')->constrained()->onDelete('restrict');
            $table->foreignId('municipality_id')->constrained()->onDelete('restrict');

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
