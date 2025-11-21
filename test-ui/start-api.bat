@echo off
echo Starting PHP API Server...
echo.
echo Server will be available at: http://localhost:8000
echo API endpoint: http://localhost:8000/api/v1/transactions
echo.
echo Press Ctrl+C to stop the server
echo.
cd /d %~dp0
php -S localhost:8000
pause

