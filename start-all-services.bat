@echo off
echo Starting Laravel CSV Upload Application Services...
echo.

echo Starting Laravel Development Server...
start "Laravel Server" cmd /k "php artisan serve"

echo Starting Queue Worker...
start "Queue Worker" cmd /k "php artisan queue:work --verbose"

echo Starting Reverb WebSocket Server...
start "Reverb Server" cmd /k "php artisan reverb:start"

echo.
echo All services started in separate windows!
echo.
echo Services running:
echo - Laravel Server: http://localhost:8000
echo - Queue Worker: Processing background jobs
echo - Reverb Server: WebSocket on port 8080
echo.
echo Press any key to close this window...
pause >nul