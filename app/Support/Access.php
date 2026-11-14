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
        'manage_users' => 'Add and edit staff accounts',
        'manage_roles' => 'Create and edit roles and permissions',
        'manage_settings' => 'Change settings and backups',
        'view_admin' => 'Open the admin panel',
    ];

    /** How the permission list is grouped on the roles and user screens. */
    public const PERMISSION_GROUPS = [
        'Vehicles' => ['vehicles.'],
        'Clearing jobs (cargo)' => ['cargo.'],
        'Containers and documents' => ['containers.', 'documents.'],
        'Deposits' => ['deposits.'],
        'Clearing officers' => ['clearing.'],
        'Money' => ['money.', 'finance.'],
        'Customers and sales' => ['customers.', 'leads.', 'quotes.', 'inventory.'],
        'Admin' => ['records.', 'view_reports', 'manage_', 'view_admin', 'catalog.'],
    ];

    /** Home screens a role can land on; the first two show money. */
    public const HOME_LABELS = [
        'ceo' => 'Whole business (CEO view)',
        'business' => 'Sales and money (Head of Business view)',
        'logistics' => 'Containers and shipping (Logistics view)',
        'puncher' => 'Data entry (Puncher view)',
    ];

    private const MONEY_HOMES = ['ceo', 'business'];

    /** Permissions grouped for display: [['group' => ..., 'permissions' => [['name','label'], ...]], ...]. */
    public static function permissionGroups(): array
    {
        $groups = [];
        $placed = [];
        foreach (self::PERMISSION_GROUPS as $group => $prefixes) {
            $items = [];
            foreach (self::PERMISSIONS as $name => $label) {
                if (isset($placed[$name])) {
                    continue;
                }
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($name, $prefix)) {
                        $items[] = ['name' => $name, 'label' => $label];
                        $placed[$name] = true;
                        break;
                    }
                }
            }
            if ($items) {
                $groups[] = ['group' => $group, 'permissions' => $items];
            }
        }
        $rest = array_diff_key(self::PERMISSIONS, $placed);
        if ($rest) {
            $groups[] = ['group' => 'Other', 'permissions' => collect($rest)->map(fn ($l, $n) => ['name' => $n, 'label' => $l])->values()->all()];
        }

        return $groups;
    }

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
     * Make sure every permission and built-in role exists.
     *
     * Roles are edited in the app (Staff & roles → Roles), so this never takes
     * permissions away. A built-in role created here starts with its defaults;
     * an existing one only gains permissions that are new in this run, so a
     * future permission still reaches the roles meant to have it.
     */
    public static function syncRoles(): void
    {
        $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();
        $guard = config('auth.defaults.guard', 'web');

        $new = [];
        foreach (array_keys(self::PERMISSIONS) as $name) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            if ($permission->wasRecentlyCreated) {
                $new[] = $name;
            }
        }

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => self::ROLE_CEO, 'guard_name' => $guard])
            ->givePermissionTo(array_keys(self::PERMISSIONS));

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $grant = $role->wasRecentlyCreated ? $permissions : array_values(array_intersect($permissions, $new));
            if ($grant) {
                $role->givePermissionTo($grant);
            }
        }

        $registrar->forgetCachedPermissions();
    }

    /** The user's (single) role model, with its label/description/home columns. */
    public static function roleOf(?User $user): ?\Spatie\Permission\Models\Role
    {
        return $user?->roles->first();
    }

    public static function home(?User $user): string
    {
        if (!$user) {
            return 'puncher';
        }
        if ($user->hasRole(self::ROLE_CEO)) {
            return 'ceo';
        }

        $role = self::roleOf($user);
        $home = $role?->home ?: (self::HOMES[$role?->name] ?? 'puncher');
        if (!array_key_exists($home, self::HOME_LABELS)) {
            $home = 'puncher';
        }
        // The CEO and business homes show money; never show them to someone who cannot see it.
        if (in_array($home, self::MONEY_HOMES, true) && !$user->can('money.view')) {
            $home = $user->can('containers.manage') ? 'logistics' : 'puncher';
        }

        return $home;
    }

    public static function roleLabel(?User $user): ?string
    {
        $role = self::roleOf($user);
        if ($role?->label) {
            return $role->label;
        }
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
