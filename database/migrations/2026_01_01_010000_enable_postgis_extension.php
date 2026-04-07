<?php

use Clickbar\Magellan\Schema\MagellanSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MagellanSchema::enablePostgisIfNotExists($this->connection);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MagellanSchema::disablePostgisIfExists($this->connection);
    }
};
