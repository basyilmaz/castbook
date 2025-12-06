@echo off
:: Silent runner for Laravel schedule

:: --- Ayarla: kendi php ve proje yollarına göre güncelle ---
set "PHP_PATH=C:\xampp\php\php.exe"
set "PROJECT_PATH=C:\YazilimProjeler\Muhasebe"

:: Start PHP artisan schedule:run silently via PowerShell
powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden ^
  -Command "Start-Process -FilePath '%PHP_PATH%' -ArgumentList '%PROJECT_PATH%\artisan schedule:run' -WorkingDirectory '%PROJECT_PATH%' -WindowStyle Hidden"

exit /B 0
