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
        Schema::create('projections', function (Blueprint $table) {
            $table->id();
            $table->double('px');
            $table->double('py');
            $table->geometry('start_point', subtype: 'point', srid: 31254);
            $table->double('ax');
            $table->double('ay');
            $table->timestamps();
        });

        DB::statement('
            CREATE OR REPLACE FUNCTION set_start_point_from_px_py()
            RETURNS trigger AS
            $$
            BEGIN
                -- start_point als PostGIS Point setzen
                NEW.start_point := ST_MakePoint(NEW.px, NEW.py);
                RETURN NEW;
            END;
            $$
            LANGUAGE plpgsql;
        ');

        DB::statement('
            CREATE TRIGGER projections_start_point_trigger
            BEFORE INSERT OR UPDATE ON projections
            FOR EACH ROW
            EXECUTE FUNCTION set_start_point_from_px_py();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS projections_start_point_trigger ON projections');
        DB::statement('DROP FUNCTION IF EXISTS set_start_point_from_px_py()');
        Schema::dropIfExists('projections');
    }
};
