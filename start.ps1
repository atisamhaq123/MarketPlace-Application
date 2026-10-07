param([int]$Port = 8000)
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$marketplacePhpCommand = Get-Command php -ErrorAction SilentlyContinue
$marketplacePhp = if ($marketplacePhpCommand) { $marketplacePhpCommand.Source } else { 'C:\wamp64\bin\php\php8.4.15\php.exe' }
if (-not (Test-Path -LiteralPath $marketplacePhp)) { throw 'Install PHP 8.3 or newer and add it to PATH.' }
if (-not (Test-Path -LiteralPath 'vendor/autoload.php')) { throw 'Run composer install first.' }
if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
    & $marketplacePhp artisan key:generate --no-interaction
    if ($LASTEXITCODE -ne 0) { throw 'Could not generate the application key.' }
}
New-Item -ItemType Directory -Force public/css | Out-Null
Copy-Item -LiteralPath resources/css/app.css -Destination public/css/marketplace.css
$env:XDEBUG_MODE = 'off'
& $marketplacePhp artisan serve --host=127.0.0.1 --port=$Port --no-interaction
