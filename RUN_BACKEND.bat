@echo off
title CIVILWATCH Backend
cd /d "%~dp0laravel"

echo ======================================================
echo   CIVILWATCH backend - updating Composer packages
echo ======================================================
call composer update --no-interaction

if not exist .env copy .env.example .env
findstr /r /c:"^APP_KEY=." .env >nul
if errorlevel 1 php artisan key:generate --force

echo.
echo ======================================================
echo   Starting Laravel server...
echo ======================================================
call php artisan serve

pause
