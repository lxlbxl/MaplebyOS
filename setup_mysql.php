<?php

/**
 * ADGOD-OS MySQL Setup Script
 * Use this to verify connection, create the database, and run migrations.
 */

echo "\n--- ADGOD-OS Database Setup ---\n\n";

// 1. Check for MySQL Extension
if (!extension_loaded('pdo_mysql')) {
    echo "\033[31m[ERROR] PHP MySQL extension (pdo_mysql) is NOT enabled.\033[0m\n";
    echo "Please uncomment 'extension=pdo_mysql' in C:\\php\\php.ini and restart.\n";
    exit(1);
}

// Web-friendly setup
$isWeb = php_sapi_name() !== 'cli';
if ($isWeb) {
    echo "<pre>";
    ob_implicit_flush(true);
    set_time_limit(0);
}

echo "\033[32m[OK] MySQL extension found.\033[0m\n";

// 2. Load .env
if (!file_exists('.env')) {
    echo "\033[31m[ERROR] .env file not found.\033[0m\n";
    exit(1);
}

// Simple .env parser to avoid complex dependencies if vendor not loaded
$env = [];
$lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0)
        continue;
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $env[$key] = $value;
    }
}

$conn = $env['DB_CONNECTION'] ?? 'sqlite';
if ($conn !== 'mysql') {
    echo "\033[33m[WARNING] .env DB_CONNECTION is currently '$conn'. Switching to 'mysql' temporary context...\033[0m\n";
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$db = $env['DB_DATABASE'] ?? 'adgod_os';

echo "Target: mysql://$user@$host:$port/$db\n\n";

// 3. Connect to Server (no DB selected)
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "\033[32m[OK] Connected to MySQL server successfully.\033[0m\n";
} catch (PDOException $e) {
    echo "\033[31m[ERROR] Connection failed: " . $e->getMessage() . "\033[0m\n";
    echo "Please check your .env credentials and ensure MySQL is running.\n";
    exit(1);
}

// 4. Create Database
try {
    echo "Checking database '$db'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "\033[32m[OK] Database '$db' exists or was created.\033[0m\n";
} catch (PDOException $e) {
    echo "\033[31m[ERROR] Failed to create database: " . $e->getMessage() . "\033[0m\n";
    exit(1);
}

// 5. Run Migrations
echo "\nRunning migrations...\n";

// Ensure we are using the right DB in artisan
// We can't easily change .env programmatically for the artisan process, 
// so we assume the user has updated .env OR we advise them.
if ($conn !== 'mysql') {
    echo "\033[33m[NOTE] Your .env is still set to '$conn'. Migrations might run on SQLite.\033[0m\n";
    echo "Please set DB_CONNECTION=mysql in .env before continuing for full effect.\n";

    if (!$isWeb) {
        echo "Continue anyway? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) != 'y') {
            echo "Aborted.\n";
            exit;
        }
    } else {
        echo "Running in web mode: Skipping confirmation and forcing continuation...\n";
    }
}

passthru('php artisan migrate --force');

// 5.5. Fix Database Schema (add missing columns)
echo "\nChecking and fixing database schema...\n";
try {
    $pdo->exec("USE `$db`");

    // Check and add 'role' column to users table
    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'role' column to users table...\n";
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `role` VARCHAR(50) NULL DEFAULT 'sales' AFTER `email`");
        echo "\033[32m[OK] Added 'role' column.\033[0m\n";
    } else {
        echo "[OK] 'role' column exists.\n";
    }

    // Check and add 'is_active' column to users table
    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'is_active' column to users table...\n";
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`");
        // Set all existing users as active
        $pdo->exec("UPDATE `users` SET `is_active` = 1");
        echo "\033[32m[OK] Added 'is_active' column and set existing users as active.\033[0m\n";
    } else {
        echo "[OK] 'is_active' column exists.\n";
    }

    // Check and create 'settings' table if missing
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() == 0) {
        echo "Creating 'settings' table...\n";
        $pdo->exec("
            CREATE TABLE `settings` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(255) NOT NULL UNIQUE,
                `value` TEXT NULL,
                `type` VARCHAR(50) DEFAULT 'string',
                `group` VARCHAR(100) DEFAULT 'general',
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "\033[32m[OK] Created 'settings' table.\033[0m\n";
    } else {
        echo "[OK] 'settings' table exists.\n";
    }

    // Migrate shipment statuses to new shipping-related values
    $stmt = $pdo->query("SHOW TABLES LIKE 'shipments'");
    if ($stmt->rowCount() > 0) {
        echo "Updating shipment statuses to new values...\n";
        $statusMap = [
            // 'pending' => 'received', // Keep pending as pending (Awaiting)
            'in_progress' => 'shipped',
            'completed' => 'delivered',
            'cancelled' => 'received', // fallback
        ];
        foreach ($statusMap as $oldStatus => $newStatus) {
            $pdo->exec("UPDATE `shipments` SET `status` = '$newStatus' WHERE `status` = '$oldStatus'");
        }
        echo "\033[32m[OK] Updated shipment statuses.\033[0m\n";
        echo "\033[32m[OK] Updated shipment statuses.\033[0m\n";
    }

    // Check and add 'payment_status' column to shipments table
    $stmt = $pdo->query("SHOW COLUMNS FROM `shipments` LIKE 'payment_status'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'payment_status' column to shipments table...\n";
        $pdo->exec("ALTER TABLE `shipments` ADD COLUMN `payment_status` VARCHAR(50) DEFAULT 'unpaid' AFTER `status`");
        echo "\033[32m[OK] Added 'payment_status' column.\033[0m\n";
    } else {
        echo "[OK] 'payment_status' column exists in shipments.\n";
    }

    // Check and create 'payments' table if missing
    $stmt = $pdo->query("SHOW TABLES LIKE 'payments'");

    if ($stmt->rowCount() == 0) {
        echo "Creating 'payments' table...\n";
        $pdo->exec("
            CREATE TABLE `payments` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `quote_id` BIGINT UNSIGNED NOT NULL,
                `amount` DECIMAL(10,2) NOT NULL,
                `payment_date` DATE NOT NULL,
                `payment_method` VARCHAR(255) NOT NULL,
                `transaction_reference` VARCHAR(255) NULL,
                `notes` TEXT NULL,
                `created_by` BIGINT UNSIGNED NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                `deleted_at` TIMESTAMP NULL,
                FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "\033[32m[OK] Created 'payments' table.\033[0m\n";
    } else {
        echo "[OK] 'payments' table exists.\n";
    }

    // Check and add 'shipment_id' to quotes table
    $stmt = $pdo->query("SHOW COLUMNS FROM `quotes` LIKE 'shipment_id'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'shipment_id' and inventory columns to quotes table...\n";
        $pdo->exec("ALTER TABLE `quotes` ADD COLUMN `shipment_id` BIGINT UNSIGNED NULL AFTER `created_by`");
        $pdo->exec("ALTER TABLE `quotes` ADD CONSTRAINT `quotes_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments`(`id`) ON DELETE SET NULL");

        $pdo->exec("ALTER TABLE `quotes` ADD COLUMN `inventory_type` VARCHAR(255) NULL AFTER `shipment_id`");
        $pdo->exec("ALTER TABLE `quotes` ADD COLUMN `inventory_id` BIGINT UNSIGNED NULL AFTER `inventory_type`");
        $pdo->exec("ALTER TABLE `quotes` ADD INDEX `quotes_inventory_type_inventory_id_index` (`inventory_type`, `inventory_id`)");
        echo "\033[32m[OK] Added linking columns to quotes.\033[0m\n";
    } else {
        echo "[OK] 'shipment_id' column exists in quotes.\n";
    }

} catch (PDOException $e) {
    echo "\033[31m[WARNING] Schema fix issue: " . $e->getMessage() . "\033[0m\n";
    // Continue anyway
}

echo "\nSeeding initial Roles and Permissions...\n";
passthru('php artisan db:seed --class=RolesAndPermissionsSeeder --force');

// 6. Seed Default Settings
echo "\nChecking default settings...\n";
try {
    $pdo->exec("USE `$db`"); // Ensure DB is selected for raw SQL
} catch (Exception $e) {
    // Some PDO versions might not like USE
}

$defaultSettings = [
    ['key' => 'app_name', 'value' => 'Shipping and Logistics OS', 'type' => 'string', 'group' => 'general'],
    ['key' => 'support_email', 'value' => 'support@example.com', 'type' => 'string', 'group' => 'general'],
    ['key' => 'default_currency', 'value' => 'USD', 'type' => 'string', 'group' => 'general'],
    ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general'],
    ['key' => 'smtp_host', 'value' => '', 'type' => 'string', 'group' => 'email'],
    ['key' => 'smtp_port', 'value' => '587', 'type' => 'integer', 'group' => 'email'],
    ['key' => 'smtp_encryption', 'value' => 'tls', 'type' => 'string', 'group' => 'email'],
    ['key' => 'smtp_username', 'value' => '', 'type' => 'string', 'group' => 'email'],
    ['key' => 'smtp_password', 'value' => '', 'type' => 'string', 'group' => 'email'],
    ['key' => 'vat_rate', 'value' => '20', 'type' => 'float', 'group' => 'finance'],
    ['key' => 'handling_fee', 'value' => '0', 'type' => 'float', 'group' => 'finance'],
    ['key' => 'strict_password_policy', 'value' => 'true', 'type' => 'boolean', 'group' => 'security'],
    ['key' => 'session_timeout', 'value' => 'true', 'type' => 'boolean', 'group' => 'security'],
];

$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `key` = ?");
$stmtInsert = $pdo->prepare("INSERT INTO settings (`key`, `value`, `type`, `group`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, NOW(), NOW())");

foreach ($defaultSettings as $setting) {
    $stmtCheck->execute([$setting['key']]);
    if ($stmtCheck->fetchColumn() == 0) {
        echo "Seeding default setting: {$setting['key']}...\n";
        $stmtInsert->execute([$setting['key'], $setting['value'], $setting['type'], $setting['group']]);
    }
}

// 7. Import Legacy Data Dump
$dumpFile = 'database/dumps/legacy_data.sql';
if (file_exists($dumpFile)) {
    echo "\nFound legacy data dump ($dumpFile). Importing...\n";
    $sql = file_get_contents($dumpFile);
    // Split by semicolons for basic execution, or try executing as one block if driver supports it.
    // PDO::exec allows multiple statements in some configs, but safe way is loop.
    // However, our dump uses simple inserts.

    // We'll try to run it. If it fails due to multiline config, we might need to split using a regex or simple explode if content allows.
    // Given the generator makes one INSERT per line ending with ;\n, we can split by ;\n.

    // Note: This is a basic importer.
    try {
        $pdo->exec($sql);
        echo "\033[32m[OK] Legacy data imported successfully.\033[0m\n";
    } catch (PDOException $e) {
        echo "\033[31m[ERROR] Failed to import legacy data: " . $e->getMessage() . "\033[0m\n";
        // Don't exit, just warn
    }
}

echo "\n\033[32m[DONE] Setup complete.\033[0m\n";
if ($isWeb) {
    echo "</pre>";
}
