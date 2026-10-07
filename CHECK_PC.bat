@echo off
title CivilWatch PC Check
echo ============================================
echo   CivilWatch PC health check (Windows)
echo ============================================
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0check_pc.ps1"
echo.
echo Press any key to close...
pause >nul
