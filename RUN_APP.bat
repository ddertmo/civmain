@echo off
title CIVILWATCH App
cd /d "%~dp0civ-main"

echo ======================================================
echo   CIVILWATCH - updating Flutter packages (pub upgrade)
echo ======================================================
call "C:\Users\Administrator\Downloads\flutter\bin\flutter.bat" pub upgrade

echo.
echo ======================================================
echo   Starting CivilWatch...
echo ======================================================
call "C:\Users\Administrator\Downloads\flutter\bin\flutter.bat" run

pause
