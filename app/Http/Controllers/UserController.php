<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

/**
 * Staff accounts. Each person has one of the four staff roles in App\Support\Access.
 */
class UserController extends Controller
{
    private const ROLE_DESCRIPTIONS = [
        Access::ROLE_CEO => 'Everything: money, profit, staff, settings.',
        Access::ROLE_BUSINESS => 'Customers, leads, quotes, invoices and payments. Sees costs and profit but cannot edit costs.',
        Access::ROLE_LOGISTICS => 'Containers, loading and shipping updates for Canada. No money or customer contact details.',
        Access::ROLE_PUNCHER => 'Adds cars and fixes their own entries until loaded. No money or customer contact details.',
    ];

    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles:id,name')
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
            ]);

        $stats = [
            ['name' => 'Active', 'value' => User::where('is_active', true)->count()],
            ['name' => 'Inactive', 'value' => User::where('is_active', false)->count()],
        ];

        $roles = collect(Access::ROLE_LABELS)->map(fn ($label, $name) => [
            'id' => $name,
            'name' => $label,
            'description' => self::ROLE_DESCRIPTIONS[$name],
            'users_count' => User::role($name)->count(),
        ])->values();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
            'stats' => $stats,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(array_keys(Access::ROLE_LABELS))],
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->is_active = $validated['is_active'] ?? true;
        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(array_keys(Access::ROLE_LABELS))],
            'is_active' => 'boolean',
        ]);

        if ($user->hasRole(Access::ROLE_CEO) && $validated['role'] !== Access::ROLE_CEO && User::role(Access::ROLE_CEO)->count() <= 1) {
            return redirect()->back()->with('error', 'There must always be at least one CEO / Super Admin.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if ($user->id !== $request->user()->id) {
            $user->is_active = $validated['is_active'] ?? $user->is_active;
        }
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "User {$status} successfully.");
    }
}
