<?php

use App\Support\Access;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds the container deposit and clearing officer permissions (see App\Support\Access).
 * Safe to re-run: it syncs, so editing Access and running
 * `php artisan staff:sync-roles` brings an existing install up to date.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        Access::syncRoles();
    }

    public function down(): void
    {
        // Roles are left in place: removing them would lock staff out.
    }
};
