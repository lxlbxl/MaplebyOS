<?php
// Save as: public/fix_deployment.php
// URL: https://adgod.d20.com.ng/public/fix_deployment.php

header('Content-Type: text/plain');
echo "=== Deployment Fixer ===\n\n";

$docRoot = $_SERVER['DOCUMENT_ROOT']; // Likely .../adgod.d20.com.ng
$repoRoot = dirname(__DIR__) . '/'; // Going up one level from public/

echo "Document Root: $docRoot\n";
echo "Script Dir: " . __DIR__ . "\n";
echo "Project Root (assumed): " . realpath($repoRoot) . "\n\n";

// 1. Check Build Location
echo "--- 1. Asset Build Location ---\n";
$rootBuild = $repoRoot . 'build';
$publicBuild = __DIR__ . '/build';

if (is_dir($rootBuild)) {
    echo "WARNING: 'build' found in Project Root (" . realpath($rootBuild) . "). This is WRONG location.\n";
    echo "ACTION: Move 'build' folder into 'public/'.\n";
} else {
    echo "OK: 'build' not found in Project Root.\n";
}

if (is_dir($publicBuild)) {
    echo "SUCCESS: 'build' found in 'public/'. Correct location.\n";
} else {
    echo "ERROR: 'build' MISSING from 'public/'. Assets will 404.\n";
}

// 2. Check Permissions (Common cause of 500 Errors)
echo "\n--- 2. Permission Checks (for 500 Error) ---\n";
$dirsToCheck = [
    'storage',
    'storage/logs',
    'storage/framework',
    'storage/framework/views',
    'bootstrap/cache'
];

foreach ($dirsToCheck as $dir) {
    $fullPath = $repoRoot . $dir;
    if (is_dir($fullPath)) {
        $path = realpath($fullPath);
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $isWritable = is_writable($path);
        echo "[$perms] $dir - " . ($isWritable ? "Writable (OK)" : "NOT WRITABLE (Fix Needed)") . "\n";
    } else {
        echo "MISSING: $dir\n";
    }
}

// 3. .htaccess Check
echo "\n--- 3. .htaccess Check ---\n";
$rootHtaccess = $repoRoot . '.htaccess';
if (file_exists($rootHtaccess)) {
    echo "WARNING: Found .htaccess in Project Root.\n";
    echo "This might be causing the 403 Forbidden error on /.\n";
    echo "Content Preview:\n" . htmlspecialchars(substr(file_get_contents($rootHtaccess), 0, 100)) . "...\n";
} else {
    echo "OK: No .htaccess in Project Root.\n";
}

echo "\n=== End Report ===\n";
