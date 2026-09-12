$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$php = 'C:\xampp\php\php.exe'
if (-not (Test-Path $php)) { throw "PHP do XAMPP não encontrado em $php" }

$phpFiles = Get-ChildItem -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch '\\uploads\\' }
foreach ($file in $phpFiles) {
    & $php -l $file.FullName *> $null
    if ($LASTEXITCODE -ne 0) { throw "Sintaxe PHP inválida: $($file.FullName)" }
}

$jsFiles = Get-ChildItem js -Filter *.js
foreach ($file in $jsFiles) {
    node --check $file.FullName
    if ($LASTEXITCODE -ne 0) { throw "Sintaxe JavaScript inválida: $($file.FullName)" }
}

$sourceFiles = Get-ChildItem php,dashboard -Recurse -File | Where-Object { $_.Extension -in '.php', '.js' }
$forbidden = $sourceFiles | Select-String -Pattern 'Home@spSENAI2025|password\s*=\s*[''\"][^''\"]+|senha\s*=\s*[''\"][^''\"]+' -AllMatches
if ($forbidden) { throw 'Possível credencial hardcoded encontrada no código da aplicação.' }

$required = @(
    'php/session.php',
    'php/csrf.php',
    'php/solicitar_recuperacao.php',
    'php/redefinir_senha.php',
    'database/schema.sql',
    'database/migrations/001_create_anotacoes.sql',
    'uploads/perfil/.htaccess'
)
foreach ($path in $required) {
    if (-not (Test-Path $path)) { throw "Arquivo obrigatório ausente: $path" }
}

$schema = Get-Content -Raw database/schema.sql
foreach ($table in @('anotacoes', 'password_resets')) {
    if ($schema -notmatch "CREATE TABLE IF NOT EXISTS $table") { throw "Tabela ausente no schema canônico: $table" }
}

Write-Output "SMOKE_OK PHP=$($phpFiles.Count) JS=$($jsFiles.Count)"
