@echo off
REM ============================================
REM Cache Clearing Script for CricApp
REM Clears all cache files and temporary data
REM ============================================

echo.
echo ========================================
echo   CricApp Cache Clearing Utility
echo ========================================
echo.

REM Navigate to project root
cd /d "%~dp0"

echo [1/6] Clearing PHP OpCache...
REM Restart Apache to clear OpCache
net stop Apache2.4 2>nul
timeout /t 2 /nobreak >nul
net start Apache2.4 2>nul
echo     ✓ OpCache cleared

echo.
echo [2/6] Clearing session files...
if exist "tmp\sessions\*" (
    del /q "tmp\sessions\*" 2>nul
    echo     ✓ Session files cleared
) else (
    echo     ℹ No session files found
)

echo.
echo [3/6] Clearing log files...
if exist "logs\*" (
    del /q "logs\*.log" 2>nul
    echo     ✓ Log files cleared
) else (
    echo     ℹ No log files found
)

echo.
echo [4/6] Clearing cache directory...
if exist "cache\*" (
    del /q /s "cache\*" 2>nul
    echo     ✓ Cache directory cleared
) else (
    echo     ℹ No cache directory found
)

echo.
echo [5/6] Clearing browser cache hints...
REM Add timestamp to force browser reload
echo     Creating cache-bust timestamp...
echo %date% %time% > .cache-version
echo     ✓ Cache-bust file created

echo.
echo [6/6] Clearing temp files...
if exist "tmp\*" (
    del /q "tmp\*.tmp" 2>nul
    del /q "tmp\*.cache" 2>nul
    echo     ✓ Temp files cleared
) else (
    echo     ℹ No temp files found
)

echo.
echo ========================================
echo   Cache Clearing Complete!
echo ========================================
echo.
echo Next steps:
echo   1. Hard refresh browser (Ctrl+Shift+R)
echo   2. Clear browser cache manually
echo   3. Restart browser if needed
echo.
pause
