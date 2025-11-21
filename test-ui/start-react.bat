@echo off
echo Starting React Development Server...
echo.
echo Make sure you've run: npm install (first time only)
echo.
echo Server will be available at: http://localhost:3000
echo.
echo Press Ctrl+C to stop the server
echo.
cd /d %~dp0react-ui
if not exist node_modules (
    echo Installing dependencies...
    call npm install
)
call npm run dev
pause

