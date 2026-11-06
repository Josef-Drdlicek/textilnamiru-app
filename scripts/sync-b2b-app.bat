@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0sync-b2b-app.ps1"
if errorlevel 1 exit /b 1
