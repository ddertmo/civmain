@echo off
title CIVILWATCH App
setlocal

rem Tool paths: override by setting CW_FLUTTER / CW_PHP before running,
rem e.g.  set CW_FLUTTER=D:\sdk\flutter\bin\flutter.bat
if defined CW_FLUTTER (set "FLUTTER=%CW_FLUTTER%") else (set "FLUTTER=C:\Users\Administrator\Downloads\flutter\bin\flutter.bat")
if defined CW_PHP (set "PHP=%CW_PHP%") else (set "PHP=C:\xampp\php\php.exe")

cd /d "%~dp0civ-main"

echo ======================================================
echo   CIVILWATCH - fetching Flutter packages (pub get)
echo ======================================================
rem pub get installs exactly what pubspec.lock pins. (pub upgrade used to run
rem here on every launch and silently moved dependencies to newer versions.)
call "%FLUTTER%" pub get

rem Start the Laravel backend on port 8000 if it is not already running
powershell -NoProfile -Command "if (-not (Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -InformationLevel Quiet -WarningAction SilentlyContinue)) { Start-Process '%PHP%' -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=8000' -WorkingDirectory '%~dp0laravel' -WindowStyle Hidden; 'backend started on port 8000' } else { 'backend already running' }"

cd /d "%~dp0civ-main"

echo.
echo ======================================================
echo   Starting CivilWatch on Chrome...
echo ======================================================
call "%FLUTTER%" run -d chrome

pause