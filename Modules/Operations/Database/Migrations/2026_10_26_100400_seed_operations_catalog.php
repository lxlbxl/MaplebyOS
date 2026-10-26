<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Operations\Database\Seeders\OperationsCatalogSeeder;

/**
 * Load the starter catalog on migrate so suggestions work on existing installs
 * without running seeders by hand. The seeder only adds missing entries.
 */
return new class extends Migration {
    public function up(): void
    {
        (new OperationsCatalogSeeder())->run();
    }

    public function down(): void
    {
        //
    }
};
