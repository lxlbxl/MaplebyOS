<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and what each one may do. Built-in roles can be edited but not
 * deleted; the CEO / Super Admin role always has every permission.
 *
 * Someone with manage_roles who is not a Super Admin can only hand out
 * permissions they hold themselves, and cannot touch the Super Admin role.
 */
class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::query()
            ->withCount('users')
            ->with('permissions:id,name')
            ->orderByDesc('is_system')
            ->orderBy('id')
            ->get()
            ->map(fn (Role $role) => self::present($role));

        $actor = $request->user();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissionGroups' => Access::permissionGroups(),
            'homes' => collect(Access::HOME_LABELS)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values(),
            'grantable' => self::grantable($actor),
            'isSuperAdmin' => $actor->hasRole(Access::ROLE_CEO),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $role = Role::create([
            'name' => $validated['label'],
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);
        $role->forceFill([
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
            'home' => $validated['home'],
            'is_system' => false,
        ])->save();
        $role->syncPermissions($this->allowedPermissions($request, $validated['permissions'] ?? [], []));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', "Role \"{$role->label}\" created.");
    }

    public function update(Request $request, Role $role)
    {
        $actor = $request->user();
        $isCeoRole = $role->name === Access::ROLE_CEO;
        if ($isCeoRole && !$actor->hasRole(Access::ROLE_CEO)) {
            abort(403, 'Only a Super Admin can change the Super Admin role.');
        }

        $validated = $this->validated($request, $role);

        $role->forceFill([
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
            'home' => $isCeoRole ? 'ceo' : $validated['home'],
        ]);
        // Built-in role names are used in code; only custom roles are renamed.
        if (!$role->is_system) {
            $role->name = $validated['label'];
        }
        $role->save();

        if ($isCeoRole) {
            $role->syncPermissions(array_keys(Access::PERMISSIONS));
        } else {
            $current = $role->permissions()->pluck('name')->all();
            $role->syncPermissions($this->allowedPermissions($request, $validated['permissions'] ?? [], $current));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', "Role \"{$role->label}\" saved.");
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->is_system) {
            return redirect()->back()->with('error', 'Built-in roles cannot be deleted. You can change what they are allowed to do instead.');
        }
        $count = User::role($role->name)->count();
        if ($count > 0) {
            return redirect()->back()->with('error', "Move the {$count} " . ($count === 1 ? 'person' : 'people') . ' with this role to another role first.');
        }

        $label = $role->label ?: $role->name;
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', "Role \"{$label}\" deleted.");
    }

    public static function present(Role $role): array
    {
        $isCeo = $role->name === Access::ROLE_CEO;

        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => $role->label ?: $role->name,
            'description' => $role->description,
            'home' => $isCeo ? 'ceo' : ($role->home ?: (Access::HOMES[$role->name] ?? 'puncher')),
            'is_system' => (bool) $role->is_system,
            'is_super_admin' => $isCeo,
            'users_count' => $role->users_count ?? null,
            'permissions' => $isCeo ? array_keys(Access::PERMISSIONS) : $role->permissions->pluck('name')->values()->all(),
        ];
    }

    /** Permission names this person may hand out. */
    public static function grantable(User $actor): array
    {
        if ($actor->hasRole(Access::ROLE_CEO)) {
            return array_keys(Access::PERMISSIONS);
        }

        return array_values(array_filter(array_keys(Access::PERMISSIONS), fn ($p) => $actor->can($p)));
    }

    /**
     * The requested permission list, limited to what the actor may grant.
     * Permissions the actor cannot grant keep whatever the role had before.
     */
    private function allowedPermissions(Request $request, array $requested, array $current): array
    {
        $grantable = self::grantable($request->user());
        $requested = array_values(array_intersect($requested, array_keys(Access::PERMISSIONS)));

        $kept = array_diff($current, $grantable);
        $chosen = array_intersect($requested, $grantable);

        return array_values(array_unique([...$kept, ...$chosen]));
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $rolesTable = config('permission.table_names.roles', 'roles');

        return $request->validate([
            'label' => [
                'required', 'string', 'max:60',
                Rule::unique($rolesTable, 'label')->ignore($role?->id),
                Rule::unique($rolesTable, 'name')->ignore($role?->id),
            ],
            'description' => 'nullable|string|max:500',
            'home' => ['required', Rule::in(array_keys(Access::HOME_LABELS))],
            'permissions' => 'array',
            'permissions.*' => ['string', Rule::in(array_keys(Access::PERMISSIONS))],
        ], [
            'label.unique' => 'Another role already has this name.',
        ]);
    }
}
