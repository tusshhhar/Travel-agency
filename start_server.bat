@echo off
title Bishnoi Travels - Local Server
echo ====================================================
echo Starting Bishnoi Travels Local Development Server...
echo ====================================================
echo.
echo Customer Portal: http://localhost:8000
echo Admin Portal:    http://localhost:8000/admin/login.php
echo.
echo Press Ctrl+C to stop the server at any time.
echo ====================================================
echo.
"C:\Users\Tushar\.gemini\antigravity-ide\scratch\php_bin\php.exe" -S localhost:8000 -t "%~dp0"
pause
