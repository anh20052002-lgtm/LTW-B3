@echo off
setlocal EnableExtensions DisableDelayedExpansion
title Nexa CRM - Khoi dong web
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\start-local.ps1"
set "NEXA_EXIT=%ERRORLEVEL%"
if not "%NEXA_EXIT%"=="0" if not "%NEXA_LAUNCH_NO_BROWSER%"=="1" pause
exit /b %NEXA_EXIT%
