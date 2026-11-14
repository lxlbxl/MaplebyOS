<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Staff accounts. Each person has one role (managed under Roles) and can have
 * individual overrides: extra permissions their role lacks ("allow", stored as
 * Spatie direct permissions) or role permissions taken away ("deny", stored in
 * users.denied_permissions).
 *
 * Someone with manage_users who is not a Super Admin cannot make or edit
 * Super Admins and can only allow permissions they hold themselves.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['roles:id,name,label', 'permissions:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role && $request->role !== 'all', fn ($query) => $query->role($request->role))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active !== false,
                'role' => $user->roles->first()?->name,
                'role_label' => Access::roleLabel($user),
                'allow' => $user->permissions->pluck('name')->values()->all(),
                'deny' => array_values($user->denied_permissions ?? []),
            ]);

        $stats = [
            ['name' => 'Active', 'value' => User::where('is_active', true)->count()],
            ['name' => 'Inactive', 'value' => User::where('is_active', false)->count()],
        ];

        $actor = $request->user();
        $roles = Role::query()->withCount('users')->with('permissions:id,name')
            ->orderByDesc('is_system')->orderBy('id')->get()
            ->map(fn (Role $role) => RoleController::present($role))
            ->map(fn (array $r) => [...$r, 'id' => $r['name'], 'name' => $r['label']])
            ->values();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
            'stats' => $stats,
            'roles' => $roles,
            'permissionGroups' => Access::permissionGroups(),
            'grantable' => RoleController::grantable($actor),
            'isSuperAdmin' => $actor->hasRole(Access::ROLE_CEO),
            'currentUserId' => $actor->id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::exists(config('permission.table_names.roles', 'roles'), 'name')],
            'is_active' => 'boolean',
            ...$this->overrideRules(),
        ]);
        $this->guardRole($request, $validated['role']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->is_active = $validated['is_active'] ?? true;
        $user->save();
        $user->syncRoles([$validated['role']]);
        $this->applyOverrides($request, $user, $validated);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $this->guardTarget($request, $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::exists(config('permission.table_names.roles', 'roles'), 'name')],
            'is_active' => 'boolean',
            ...$this->overrideRules(),
        ]);
        $this->guardRole($request, $validated['role']);

        if ($user->hasRole(Access::ROLE_CEO) && $validated['role'] !== Access::ROLE_CEO && User::role(Access::ROLE_CEO)->count() <= 1) {
            return redirect()->back()->with('error', 'There must always be at least one CEO / Super Admin.');
        }
        $self = $user->id === $request->user()->id;
        if ($self && !$request->user()->hasRole(Access::ROLE_CEO) && $validated['role'] !== $user->roles->first()?->name) {
            return redirect()->back()->with('error', 'You cannot change your own role.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!$self) {
            $user->is_active = $validated['is_active'] ?? $user->is_active;
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        $user->syncRoles([$validated['role']]);
        // Your own overrides stay as they are, so nobody locks themselves out by accident.
        if (!$self) {
            $this->applyOverrides($request, $user, $validated);
        }

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }
        $this->guardTarget($request, $user);
        if ($user->hasRole(Access::ROLE_CEO) && User::role(Access::ROLE_CEO)->count() <= 1) {
            return redirect()->back()->with('error', 'There must always be at least one CEO / Super Admin.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }
        $this->guardTarget($request, $user);

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "User {$status} successfully.");
    }

    private function overrideRules(): array
    {
        $names = array_keys(Access::PERMISSIONS);

        return [
            'allow' => 'array',
            'allow.*' => ['string', Rule::in($names)],
            'deny' => 'array',
            'deny.*' => ['string', Rule::in($names)],
        ];
    }

    /**
     * Save the user's own allow/deny lists. Only overrides the actor could grant
     * are changed; anything else the user already had is left untouched.
     */
    private function applyOverrides(Request $request, User $user, array $validated): void
    {
        if (!array_key_exists('allow', $validated) && !array_key_exists('deny', $validated)) {
            return;
        }
        $grantable = RoleController::grantable($request->user());

        $allow = array_values(array_intersect($validated['allow'] ?? [], $grantable));
        $deny = array_values(array_intersect(array_diff($validated['deny'] ?? [], $allow), $grantable));

        $currentAllow = $user->permissions()->pluck('name')->all();
        $currentDeny = $user->denied_permissions ?? [];

        $user->syncPermissions(array_values(array_unique([...array_diff($currentAllow, $grantable), ...$allow])));
        $user->denied_permissions = array_values(array_unique([...array_diff($currentDeny, $grantable), ...$deny])) ?: null;
        $user->save();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function guardRole(Request $request, string $role): void
    {
        if ($role === Access::ROLE_CEO && !$request->user()->hasRole(Access::ROLE_CEO)) {
            abort(403, 'Only a Super Admin can make someone a Super Admin.');
        }
    }

    private function guardTarget(Request $request, User $user): void
    {
        if ($user->hasRole(Access::ROLE_CEO) && !$request->user()->hasRole(Access::ROLE_CEO)) {
            abort(403, 'Only a Super Admin can change a Super Admin.');
        }
    }
}
