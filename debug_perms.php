<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@adgod-os.com';
$user = User::where('email', $email)->first();

echo "--- Debugging User Permissions ---\n";
if ($user) {
    echo "User found: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";

    echo "Checking 'Super Admin' role...\n";
    if ($user->hasRole('Super Admin')) {
        echo "✅ User has 'Super Admin' role.\n";
    } else {
        echo "❌ User DOES NOT have 'Super Admin' role.\n";
    }

    echo "\nChecking permissions directly...\n";
    $permissions = ['view_admin', 'manage_users', 'manage_roles'];
    foreach ($permissions as $perm) {
        if ($user->can($perm)) {
            echo "✅ User can '$perm'.\n";
        } else {
            echo "❌ User CANNOT '$perm'.\n";
        }
    }

    echo "\nChecking Gate...\n";
    if (Illuminate\Support\Facades\Gate::allows('view_admin')) {
        echo "✅ Gate allows 'view_admin'.\n";
    } else {
        echo "❌ Gate DENIES 'view_admin'.\n";
    }

    echo "\nChecking Filament Access...\n";
    $panel = new \Filament\Panel();
    if ($user->canAccessPanel($panel)) {
        echo "✅ User canAccessPanel.\n";
    } else {
        echo "❌ User canAccessPanel FAILED.\n";
    }

} else {
    echo "User with email $email not found.\n";
}
