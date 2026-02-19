@echo off
SETLOCAL EnableDelayedExpansion

echo ====================================================
echo  Certificate Generator: Python Environment Setup
echo ====================================================

:: 1. Check if Python is installed
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Python is not installed or not in your PATH.
    echo Please install Python from https://www.python.org/
    pause
    exit /b
)

:: 2. Upgrade pip to the latest version
echo [*] Upgrading pip...
python -m pip install --upgrade pip

:: 3. Check if requirements.txt exists
if not exist "requirements.txt" (
    echo [ERROR] requirements.txt not found in this directory.
    pause
    exit /b
)

:: 4. Install requirements
echo [*] Installing dependencies from requirements.txt...
python -m pip install -r requirements.txt

if %errorlevel% equ 0 (
    echo.
    echo ====================================================
    echo  [SUCCESS] Environment is ready to go!
    echo ====================================================
) else (
    echo.
    echo [ERROR] Some packages failed to install. Check the logs above.
)

pause