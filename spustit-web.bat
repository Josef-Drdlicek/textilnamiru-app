@echo off
cd /d "%~dp0"
set PHP=C:\Users\lucie\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe
echo Web bezi na http://127.0.0.1:8000/
echo Toto okno nechte otevrene.
"%PHP%" artisan serve --host=127.0.0.1 --port=8000
pause
