# Spuštění aplikace pro lokální vývoj: aktuální JavaScript a styly, databáze, server.
# Použití (z kořene projektu):  powershell -ExecutionPolicy Bypass -File scripts\spustit.ps1
# Aplikace pak běží na http://127.0.0.1:8000, okno nech otevřené, zastavíš ji Ctrl+C.

$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)

# 1) Starý server na portu 8000 zastavit, ať se nespouští dvakrát.
$old = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
foreach ($conn in $old) {
    $proc = Get-Process -Id $conn.OwningProcess -ErrorAction SilentlyContinue
    if ($proc -and $proc.ProcessName -eq 'php') {
        Write-Host "Zastavuji starý server (PID $($proc.Id))..."
        Stop-Process -Id $proc.Id -Force
    }
}

# 2) Zbytek po npm run dev: bez běžícího Vite by stránka hledala skripty, které neexistují.
if (Test-Path 'public\hot') {
    Remove-Item 'public\hot'
}

# 3) JavaScript a styly sestavit z aktuálního kódu.
Write-Host 'Sestavuji JavaScript a styly (npm run build)...'
npm run build
if ($LASTEXITCODE -ne 0) { throw 'npm run build selhal.' }

# 4) Databáze na aktuální verzi.
Write-Host 'Aktualizuji databázi (php artisan migrate)...'
php artisan migrate --force
if ($LASTEXITCODE -ne 0) { throw 'php artisan migrate selhal.' }

# 5) Server. `php artisan serve` na Windows padá na diakritice v cestě, proto router server.php.
Write-Host ''
Write-Host 'Aplikace běží na http://127.0.0.1:8000  (přihlášení do zakázek: /b2b)'
Write-Host 'Zastavíš ji klávesami Ctrl+C.'
php -S 127.0.0.1:8000 -t public server.php
