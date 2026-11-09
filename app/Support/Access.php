<?php

namespace App\Support;

use App\Models\User;

/**
 * The staff roles and what each one may see and do.
 *
 * This is the single source of truth: a migration syncs it into the
 * Spatie roles/permissions tables, routes use the permission names as
 * middleware, and models hide restricted fields from users without them.
 * "Super Admin" (the CEO) passes every check through Gate::before.
 */
final class Access
{
    public const ROLE_CEO = 'Super Admin';
    public const ROLE_BUSINESS = 'Head of Business';
    public const ROLE_LOGISTICS = 'Logistics Officer';
    public const ROLE_PUNCHER = 'Puncher';

    /** Older role kept so existing logins keep working; treated like a Puncher. */
    public const ROLE_LEGACY_STAFF = 'Staff';

    public const ROLE_LABELS = [
        self::ROLE_CEO => 'CEO / Super Admin',
        self::ROLE_BUSINESS => 'Head of Business',
        self::ROLE_LOGISTICS => 'Logistics Officer',
        self::ROLE_PUNCHER => 'Puncher / Office Support',
    ];

    /** Which home screen each role lands on. */
    public const HOMES = [
        self::ROLE_CEO => 'ceo',
        self::ROLE_BUSINESS => 'business',
        self::ROLE_LOGISTICS => 'logistics',
        self::ROLE_PUNCHER => 'puncher',
        self::ROLE_LEGACY_STAFF => 'puncher',
    ];

    public const PERMISSIONS = [
        // Vehicles
        'vehicles.view' => 'See vehicles',
        'vehicles.create' => 'Add vehicles',
        'vehicles.edit' => 'Edit any vehicle',
        'vehicles.edit_own' => 'Edit vehicles they added, until loaded',
        'vehicles.status' => 'Change vehicle status',
        'vehicles.bulk' => 'Bulk-update vehicles',
        'vehicles.import' => 'Import the tracking sheet',
        // General cargo clearing jobs (Nigeria)
        'cargo.view' => 'See clearing jobs',
        'cargo.manage' => 'Create and edit clearing jobs',
        'cargo.status' => 'Move clearing jobs through stages',
        // Containers
        'containers.view' => 'See containers',
        'containers.manage' => 'Create containers, assign cars, move stages',
        'documents.upload' => 'Scan or upload documents on cars and containers',
        // Container deposits paid to shipping lines, and their refunds
        'deposits.view' => 'See container deposits and refunds',
        'deposits.manage' => 'Record deposits, follow up and update refunds',
        'deposits.oversee' => 'Confirm refunds, write off, change who follows up',
        // Clearing officers
        'clearing.view' => 'See clearing officers and who handled each clearing',
        'clearing.manage' => 'Add clearing officers, assign and reassign them, log their work',
        'clearing.override' => 'Move credit for clearing work from one officer to another',
        'catalog.manage' => 'Edit the makes/models/lines catalog',
        // Money
        'money.view' => 'See costs, purchase prices and profit',
        'money.edit_costs' => 'Edit purchase prices, cost lines and freight cost',
        'finance.manage' => 'Customer charges, invoices and payments',
        // Customers and sales
        'customers.view' => 'See customer list and contact details',
        'customers.manage' => 'Add and edit customers',
        'leads.manage' => 'Manage leads',
        'quotes.manage' => 'Manage quotes',
        'inventory.manage' => 'Manage cars for sale',
        // Admin
        'records.delete' => 'Delete records',
        'view_reports' => 'See business totals',
        'manage_users' => 'Manage staff and roles',
        'manage_settings' => 'Change settings and backups',
        'view_admin' => 'Open the admin panel',
    ];

    public const ROLE_PERMISSIONS = [
        self::ROLE_BUSINESS => [
            'vehicles.view', 'vehicles.edit', 'containers.view', 'documents.upload', 'cargo.view',
            'money.view', 'finance.manage',
            'deposits.view', 'deposits.oversee', 'clearing.view', 'clearing.manage', 'clearing.override',
            'customers.view', 'customers.manage', 'leads.manage', 'quotes.manage', 'inventory.manage',
            'view_reports',
        ],
        self::ROLE_LOGISTICS => [
            'vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.status', 'vehicles.bulk',
            'containers.view', 'containers.manage', 'documents.upload',
            'cargo.view', 'cargo.manage', 'cargo.status',
            'clearing.view', 'clearing.manage',
        ],
        self::ROLE_PUNCHER => [
            'vehicles.view', 'vehicles.create', 'vehicles.edit_own', 'containers.view', 'documents.upload', 'cargo.view',
            'deposits.view', 'deposits.manage', 'clearing.view',
        ],
        self::ROLE_LEGACY_STAFF => [
            'vehicles.view', 'vehicles.create', 'vehicles.edit_own', 'containers.view', 'documents.upload', 'cargo.view',
            'deposits.view', 'deposits.manage', 'clearing.view',
        ],
    ];

    /** Fields only shown to users holding the permission. */
    public const SHIPMENT_MONEY_FIELDS = ['purchase_price', 'total_value', 'payment_status', 'charges', 'quotes', 'duty_assessed', 'declared_value'];
    public const CONTAINER_MONEY_FIELDS = ['freight_cost', 'freight_currency'];
    public const CLIENT_CONTACT_FIELDS = [
        'email', 'phone', 'secondary_phone', 'address', 'city', 'state_province', 'country',
        'postal_code', 'tax_id', 'notes', 'source',
    ];

    /**
     * Create/refresh every permission and role so the database matches this class.
     */
    public static function syncRoles(): void
    {
        $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();
        $guard = config('auth.defaults.guard', 'web');

        foreach (array_keys(self::PERMISSIONS) as $name) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => self::ROLE_CEO, 'guard_name' => $guard])
            ->givePermissionTo(array_keys(self::PERMISSIONS));

        foreach (self::ROLE_PERMISSIONS as $role => $permissions) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => $guard])
                ->syncPermissions($permissions);
        }

        $registrar->forgetCachedPermissions();
    }

    public static function home(?User $user): string
    {
        if (!$user) {
            return 'puncher';
        }
        foreach (self::HOMES as $role => $home) {
            if ($user->hasRole($role)) {
                return $home;
            }
        }

        return 'puncher';
    }

    public static function roleLabel(?User $user): ?string
    {
        foreach (self::ROLE_LABELS as $role => $label) {
            if ($user?->hasRole($role)) {
                return $label;
            }
        }

        return $user?->roles->first()?->name;
    }

    /**
     * Every permission as a true/false map, for the frontend to hide what
     * the server already blocks.
     */
    public static function abilities(?User $user): array
    {
        $map = [];
        foreach (array_keys(self::PERMISSIONS) as $permission) {
            $map[$permission] = (bool) $user?->can($permission);
        }

        return $map;
    }
}
