# Sestaví JS aplikaci a zkopíruje ukazka-online.html k zdroji i do Laravel resources.
$ErrorActionPreference = 'Stop'

$appRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$jsPublic = [System.IO.Path]::GetFullPath((Join-Path $appRoot '..\Webová aplikace\nová aplikace\public'))
$buildScript = Join-Path $jsPublic 'build-standalone.js'
$built = Join-Path $jsPublic 'ukazka-online.html'
$copyUp = Join-Path (Split-Path -Parent $jsPublic) 'ukazka-online.html'
$destDir = Join-Path $appRoot 'resources\b2b-app'
$destApp = Join-Path $destDir 'ukazka-online.html'

if (-not (Test-Path -LiteralPath $buildScript)) {
    throw "Nenalezen build: $buildScript"
}

Push-Location -LiteralPath $jsPublic
try {
    & node '.\build-standalone.js'
    if ($LASTEXITCODE -ne 0) {
        throw "Build selhal (kód $LASTEXITCODE). Nic se nekopírovalo."
    }
} finally {
    Pop-Location
}

if (-not (Test-Path -LiteralPath $built)) {
    throw "Build nevytvořil soubor: $built"
}

New-Item -ItemType Directory -Force -Path $destDir | Out-Null
Copy-Item -LiteralPath $built -Destination $copyUp -Force
Copy-Item -LiteralPath $built -Destination $destApp -Force

Write-Host 'Zkopírováno:'
Write-Host "  $copyUp"
Write-Host "  $destApp"
