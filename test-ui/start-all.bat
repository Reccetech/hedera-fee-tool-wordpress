@echo off
echo ========================================
echo  Hedera Fee Calculator - Test UI
echo ========================================
echo.
echo This will start both servers in separate windows.
echo.
echo Window 1: PHP API Server (port 8000)
echo Window 2: React Dev Server (port 3000)
echo.
echo After both start, open: http://localhost:3000
echo.
pause

start "PHP API Server" cmd /k "cd /d %~dp0 && php -S localhost:8000"
timeout /t 2 /nobreak >nul
start "React Dev Server" cmd /k "cd /d %~dp0react-ui && if not exist node_modules (npm install) && npm run dev"

echo.
echo Both servers are starting...
echo.
echo Open http://localhost:3000 in your browser when ready!
echo.
pause

