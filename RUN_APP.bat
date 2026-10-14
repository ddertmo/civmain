@echo off
title CIVILWATCH App
cd /d "%~dp0civ-main"

echo ======================================================
echo   CIVILWATCH - updating Flutter packages (pub upgrade)
echo ======================================================
call "C:\Users\Administrator\Downloads\flutter\bin\flutter.bat" pub upgrade

rem Start the Laravel backend on port 8000 if it is not already running
powershell -NoProfile -Command "if (-not (Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -InformationLevel Quiet -WarningAction SilentlyContinue)) { Start-Process 'C:\xampp\php\php.exe' -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=8000' -WorkingDirectory '%~dp0laravel' -WindowStyle Hidden; 'backend started on port 8000' } else { 'backend already running' }"

cd /d "%~dp0civ-main"

echo.
echo ======================================================
echo   Starting CivilWatch on Chrome...
echo ======================================================
call "C:\Users\Administrator\Downloads\flutter\bin\flutter.bat" run -d chrome

pause
