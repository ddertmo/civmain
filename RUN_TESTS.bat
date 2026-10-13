@echo off
setlocal EnableDelayedExpansion
title CIVILWATCH - Playwright E2E
color 0E

rem ============================================================
rem   CivilWatch E2E tests (Playwright)
rem   Runs the required browser tests against the web admin.
rem   Warns but never blocks the app if it cannot run.
rem ============================================================

set "REPO=%~dp0"
set "E2E=%REPO%e2e"

rem --- Locate a PHP binary (XAMPP default) for the auto-started backend ----
if not defined PHP_BIN (
  if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
  ) else (
    for %%P in (php.exe) do if not "%%~$PATH:P"=="" set "PHP_BIN=php"
  )
)
if defined PHP_BIN echo   PHP_BIN = %PHP_BIN%

rem --- Node is required ----------------------------------------------------
where node >nul 2>&1
if errorlevel 1 (
  echo.
  echo   [WARNING] Node.js not found on PATH - skipping Playwright E2E.
  echo   Install Node 18+ then run this again.
  echo.
  exit /b 1
)

cd /d "%E2E%" || (
  echo   [WARNING] e2e\ folder not found - skipping Playwright E2E.
  exit /b 1
)

rem --- Install dependencies / browsers if missing --------------------------
if not exist "node_modules" (
  echo   Installing Playwright dependencies (first run)...
  call npm install || (
    echo   [WARNING] npm install failed - skipping Playwright E2E.
    exit /b 1
  )
)

call npx playwright install chromium >nul 2>&1

rem --- Run the suite -------------------------------------------------------
echo.
echo ============================================================
echo   Running Playwright E2E ...
echo ============================================================
call npx playwright test
set "RESULT=%errorlevel%"

if not "%RESULT%"=="0" (
  echo.
  echo   [WARNING] Playwright E2E FAILED. Open the report for details:
  echo             npx playwright show-report
  echo             (artifacts in e2e\test-results\)
) else (
  echo.
  echo   Playwright E2E: ALL TESTS PASSED
)

if /i "%~1"=="--keep-open" (
  call npx playwright show-report
)

exit /b %RESULT%
