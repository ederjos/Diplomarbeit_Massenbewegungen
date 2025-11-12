<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('measurement_values', function (Blueprint $table) {
            $table->id();
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->foreignId('point_id')->constrained()->onDelete('cascade');
            $table->foreignId('measurement_id')->constrained()->onDelete('cascade');
            $table->foreignId('addition_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });

        /**
         * Prompt (ChatGPT GPT-5 mini): https://chatgpt.com/share/691317fe-685c-800a-9c4f-41e44ffe3ded
         * "sag mir bitte genau welche schritte ich ausführen muss, damit diese tabelle erstellt werden kann als migration: CREATE TABLE measurement_values ( id SERIAL PRIMARY KEY, name TEXT, geom geometry(PointZ, 31254) );"
         */
        DB::statement("ALTER TABLE measurement_values ADD COLUMN geom geometry(PointZ, 31254)");

        // Automatically set the values for the postgis geometry object based on x,y,z
        DB::statement("
            CREATE OR REPLACE FUNCTION set_geom_from_xyz()
            RETURNS trigger AS
            $$
            BEGIN
                NEW.geom := ST_MakePoint(NEW.x, NEW.y, NEW.z);
                RETURN NEW;
            END;
            $$
            LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER measurement_values_geom_trigger
            BEFORE INSERT OR UPDATE ON measurement_values
            FOR EACH ROW
            EXECUTE FUNCTION set_geom_from_xyz();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS measurement_values_geom_trigger ON measurement_values");
        DB::statement("DROP FUNCTION IF EXISTS set_geom_from_xyz()");
        Schema::dropIfExists('measurement_values');
    }
};
