<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This migration changes ownership_documents from boolean to JSON/TEXT
     * to store document paths and metadata.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL: Use raw SQL to alter column type
            DB::statement('ALTER TABLE shipments MODIFY ownership_documents JSON NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support column type changes directly
            // The column will work as TEXT which is compatible with JSON casting in Laravel
            // No action needed - Laravel's array cast handles it
        } else {
            // PostgreSQL and others
            Schema::table('shipments', function (Blueprint $table) {
                $table->json('ownership_documents')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE shipments MODIFY ownership_documents TINYINT(1) DEFAULT 0');
        } elseif ($driver === 'sqlite') {
            // SQLite - no action needed
        } else {
            Schema::table('shipments', function (Blueprint $table) {
                $table->boolean('ownership_documents')->default(false)->change();
            });
        }
    }
};
