<?php
// debug_error.php
// Place this in your public folder or root to read the latest error logs.

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "Log file not found at: $logFile";
    exit;
}

// Increased to 300 to capture full stack traces
$lines = 300;
$data = file($logFile);
$data = array_slice($data, -$lines);

echo "<h1>Last $lines lines of laravel.log</h1>";
echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc; overflow: auto; white-space: pre-wrap;'>";
foreach ($data as $line) {
    echo htmlspecialchars($line);
}
echo "</pre>";
