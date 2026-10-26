@echo off
echo ============================================
echo   MaplebyOS Deployment Builder
echo ============================================
echo.

set SOURCE=c:\Users\Alex\TraeCoder\MaplebyOS
set DEST=%SOURCE%\deploy_package

echo [1/5] Cleaning old deploy_package...
if exist "%DEST%" rmdir /s /q "%DEST%"
mkdir "%DEST%"
mkdir "%DEST%\public_html"

echo [2/5] Copying Laravel app files (for ABOVE public_html)...
:: Core Laravel directories
xcopy "%SOURCE%\app" "%DEST%\app\" /E /I /Q /Y
xcopy "%SOURCE%\bootstrap" "%DEST%\bootstrap\" /E /I /Q /Y
xcopy "%SOURCE%\config" "%DEST%\config\" /E /I /Q /Y
xcopy "%SOURCE%\database" "%DEST%\database\" /E /I /Q /Y
xcopy "%SOURCE%\Modules" "%DEST%\Modules\" /E /I /Q /Y
xcopy "%SOURCE%\resources" "%DEST%\resources\" /E /I /Q /Y
xcopy "%SOURCE%\routes" "%DEST%\routes\" /E /I /Q /Y
xcopy "%SOURCE%\vendor" "%DEST%\vendor\" /E /I /Q /Y

:: Storage directories (empty structure)
mkdir "%DEST%\storage\app\public"
mkdir "%DEST%\storage\framework\cache\data"
mkdir "%DEST%\storage\framework\sessions"
mkdir "%DEST%\storage\framework\views"
mkdir "%DEST%\storage\logs"

:: Core files
copy /y "%SOURCE%\artisan" "%DEST%\"
copy /y "%SOURCE%\composer.json" "%DEST%\"
copy /y "%SOURCE%\composer.lock" "%DEST%\"
copy /y "%SOURCE%\.env.example" "%DEST%\"
copy /y "%SOURCE%\modules_statuses.json" "%DEST%\"

echo [3/5] Copying public files (for public_html)...
:: Public directory contents
xcopy "%SOURCE%\public\build" "%DEST%\public_html\build\" /E /I /Q /Y
xcopy "%SOURCE%\public\css" "%DEST%\public_html\css\" /E /I /Q /Y
xcopy "%SOURCE%\public\js" "%DEST%\public_html\js\" /E /I /Q /Y
xcopy "%SOURCE%\public\images" "%DEST%\public_html\images\" /E /I /Q /Y
xcopy "%SOURCE%\public\website" "%DEST%\public_html\website\" /E /I /Q /Y
copy /y "%SOURCE%\public\.htaccess" "%DEST%\public_html\"
copy /y "%SOURCE%\public\index.php" "%DEST%\public_html\"
copy /y "%SOURCE%\public\manifest.json" "%DEST%\public_html\"
copy /y "%SOURCE%\public\robots.txt" "%DEST%\public_html\"
copy /y "%SOURCE%\public\service-worker.js" "%DEST%\public_html\"
if exist "%SOURCE%\public\favicon.ico" copy /y "%SOURCE%\public\favicon.ico" "%DEST%\public_html\"

echo [4/5] Creating storage link helper...
:: Create a PHP helper script that creates the storage symlink
(
echo ^<?php
echo // Run this file ONCE after upload to create the storage symlink
echo $target = __DIR__ . '/../storage/app/public';
echo $link = __DIR__ . '/storage';
echo if ^(file_exists^($link^)^) { echo "Storage link already exists.\n"; exit; }
echo if ^(symlink^($target, $link^)^) { echo "Storage link created successfully!\n"; }
echo else { echo "Failed to create storage link. Ask hosting to enable symlinks.\n"; }
) > "%DEST%\public_html\create_storage_link.php"

echo [5/5] Done!
echo.
echo ============================================
echo   Deployment bundle created at:
echo   %DEST%
echo ============================================
echo.
echo NEXT STEPS:
echo   1. Copy .env.example to .env and configure it
echo   2. ZIP the deploy_package folder
echo   3. Upload via cPanel File Manager
echo   4. See the upload guide for details
echo ============================================
