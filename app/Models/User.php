<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

// use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use /* HasApiTokens, */ HasFactory, Notifiable;
    use HasRoles {
        hasPermissionTo as traitHasPermissionTo;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // The Filament admin panel bypasses the staff app's per-role screens, so only the CEO gets in.
        return $this->hasRole(\App\Support\Access::ROLE_CEO) || $this->can('view_admin');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'denied_permissions' => 'array',
        ];
    }

    /**
     * Spatie's check, plus per-user denials: a permission denied to this user
     * is refused even when their role grants it. Every can()/@can/permission
     * middleware check ends up here. (Super Admin passes earlier, via Gate::before.)
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $denied = $this->denied_permissions ?? [];
        if ($denied) {
            $name = match (true) {
                is_string($permission) => $permission,
                $permission instanceof \BackedEnum => (string) $permission->value,
                $permission instanceof \Spatie\Permission\Contracts\Permission => $permission->name,
                is_int($permission) => \Spatie\Permission\Models\Permission::find($permission)?->name,
                default => null,
            };
            if ($name !== null && in_array($name, $denied, true)) {
                return false;
            }
        }

        return $this->traitHasPermissionTo($permission, $guardName);
    }
}
