<?php

use App\Support\Access;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roles become editable in the app: each gets a display label, a description
 * and the home screen its people land on. Built-in roles are flagged so they
 * cannot be deleted. Users can also have permissions denied individually,
 * on top of their role (extra permissions use Spatie's direct permissions).
 */
return new class extends Migration
{
    private const BUILT_IN = [
        Access::ROLE_CEO => ['CEO / Super Admin', 'Everything: money, profit, staff, roles, settings.', 'ceo'],
        Access::ROLE_BUSINESS => ['Head of Business', 'Customers, leads, quotes, invoices and payments. Sees costs and profit but cannot edit costs.', 'business'],
        Access::ROLE_LOGISTICS => ['Logistics Officer', 'Containers, loading and shipping updates. No money or customer contact details.', 'logistics'],
        Access::ROLE_PUNCHER => ['Puncher / Office Support', 'Adds cars and fixes their own entries until loaded. No money or customer contact details.', 'puncher'],
        Access::ROLE_LEGACY_STAFF => ['Staff (older role)', 'Older role kept for existing logins; works like Puncher.', 'puncher'],
    ];

    public function up(): void
    {
        $roles = config('permission.table_names.roles', 'roles');

        Schema::table($roles, function (Blueprint $table) {
            $table->string('label')->nullable()->after('name');
            $table->text('description')->nullable()->after('label');
            $table->string('home', 32)->nullable()->after('description');
            $table->boolean('is_system')->default(false)->after('home');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('denied_permissions')->nullable()->after('is_active');
        });

        foreach (self::BUILT_IN as $name => [$label, $description, $home]) {
            DB::table($roles)->where('name', $name)->update([
                'label' => $label,
                'description' => $description,
                'home' => $home,
                'is_system' => true,
            ]);
        }

        Access::syncRoles();
    }

    public function down(): void
    {
        $roles = config('permission.table_names.roles', 'roles');

        Schema::table($roles, function (Blueprint $table) {
            $table->dropColumn(['label', 'description', 'home', 'is_system']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('denied_permissions');
        });
    }
};
