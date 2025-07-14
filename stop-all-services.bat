@echo off
echo Stopping Laravel CSV Upload Application Services...
echo.

echo Stopping all PHP processes...
taskkill /F /IM php.exe 2>nul
if %errorlevel% == 0 (
    echo All PHP services stopped successfully!
) else (
    echo No PHP services were running.
)

echo.
echo All services stopped!
echo.
pause