$sourceDir = "c:\Users\Alex\TraeCoder\MaplebyOS"
$destDir = "$sourceDir\upload"
$exclude = @("upload", "node_modules", ".git", ".idea", ".vscode", "tests", ".github", "storage\framework\cache\*")

Write-Host "Preparing upload directory..."
if (Test-Path $destDir) {
    Remove-Item $destDir -Recurse -Force
}
New-Item -ItemType Directory -Path $destDir | Out-Null

$items = Get-ChildItem -Path $sourceDir -Exclude $exclude
foreach ($item in $items) {
    if ($exclude -contains $item.Name) { continue }
    
    $target = Join-Path $destDir $item.Name
    Write-Host "Copying $($item.Name)..."
    Copy-Item -Path $item.FullName -Destination $target -Recurse -Force
}

Write-Host "Cleaning up storage..."
# Ensure storage directories exist but are empty of cache
$storageDirs = @("framework/cache", "framework/sessions", "framework/views", "logs")
foreach ($dir in $storageDirs) {
    $path = Join-Path $destDir "storage/$dir"
    if (-not (Test-Path $path)) {
        New-Item -ItemType Directory -Path $path -Force | Out-Null
    }
}

Write-Host "Deployment bundle created in $destDir"
