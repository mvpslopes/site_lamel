$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
$dist = Join-Path $root "dist"

if (Test-Path $dist) {
    Remove-Item $dist -Recurse -Force
}

New-Item -ItemType Directory -Path $dist | Out-Null

Copy-Item (Join-Path $root "index.html") $dist
Copy-Item (Join-Path $root "script.js") $dist
Copy-Item (Join-Path $root "styles.css") $dist

$logoDist = Join-Path $dist "logo"
New-Item -ItemType Directory -Path $logoDist | Out-Null
Copy-Item (Join-Path $root "logo\logo_lamel_-2.png") $logoDist
Copy-Item (Join-Path $root "logo\logo_lamel_-3.png") $logoDist

$produtosRoot = Join-Path $root "produtos"
$produtosDist = Join-Path $dist "produtos"
New-Item -ItemType Directory -Path $produtosDist | Out-Null

Get-ChildItem $produtosRoot -Directory | ForEach-Object {
    $destDir = Join-Path $produtosDist $_.Name
    New-Item -ItemType Directory -Path $destDir | Out-Null
    Get-ChildItem $_.FullName -Filter "*.jpeg" | Copy-Item -Destination $destDir
}

Copy-Item (Join-Path $root ".htaccess") $dist

# Painel interno e API
Copy-Item (Join-Path $root "admin") $dist -Recurse -Force
Copy-Item (Join-Path $root "api") $dist -Recurse -Force

$configLocal = Join-Path $root "admin\config\config.local.php"
$configExample = Join-Path $root "admin\config\config.example.php"
$configDist = Join-Path $dist "admin\config\config.local.php"

if (Test-Path $configLocal) {
    Copy-Item $configLocal $configDist
} elseif (Test-Path $configExample) {
    Copy-Item $configExample $configDist
    Write-Host "Aviso: config.local.php nao encontrado. Foi copiado o exemplo." -ForegroundColor Yellow
}

$files = Get-ChildItem $dist -Recurse -File
$size = ($files | Measure-Object -Property Length -Sum).Sum
$sizeMb = [math]::Round($size / 1MB, 2)

Write-Host ""
Write-Host "Build concluido: $dist" -ForegroundColor Green
Write-Host "Arquivos: $($files.Count) | Tamanho: ${sizeMb} MB"
Write-Host ""
Write-Host "Envie o conteudo da pasta dist para public_html na Hostinger."
Write-Host "Painel interno: https://lamelmodas.com.br/admin/"
