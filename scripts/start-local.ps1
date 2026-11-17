# Windows/XAMPP: run a local demo with a database owned by this project copy.
$ErrorActionPreference = 'Stop'
$utf8 = New-Object Text.UTF8Encoding -ArgumentList $false
[Console]::OutputEncoding = $utf8
$project = (Get-Item -LiteralPath (Join-Path $PSScriptRoot '..')).FullName

function Protect-Password($Password) {
    $encrypted = [Security.Cryptography.ProtectedData]::Protect(
        $utf8.GetBytes($Password), $null, [Security.Cryptography.DataProtectionScope]::CurrentUser)
    return [Convert]::ToBase64String($encrypted)
}

function Unprotect-Password($Encrypted) {
    $bytes = [Security.Cryptography.ProtectedData]::Unprotect(
        [Convert]::FromBase64String($Encrypted), $null, [Security.Cryptography.DataProtectionScope]::CurrentUser)
    return $utf8.GetString($bytes)
}

function Find-Xampp {
    if ($env:XAMPP_HOME) {
        $candidates = @($env:XAMPP_HOME)
    } else {
        $projectParent = Split-Path -Parent (Split-Path -Parent $project)
        $phpInPath = Get-Command php.exe -CommandType Application -ErrorAction SilentlyContinue |
            Select-Object -First 1
        $candidates = @($projectParent)
        if ($phpInPath) {
            $candidates += Split-Path -Parent (Split-Path -Parent $phpInPath.Source)
        }
        $candidates += Get-PSDrive -PSProvider FileSystem |
            ForEach-Object { Join-Path $_.Root 'xampp' }
    }

    foreach ($candidate in $candidates) {
        if (!$candidate -or !(Test-Path -LiteralPath $candidate -PathType Container)) { continue }
        $missing = @('php/php.exe', 'php/php.ini', 'mysql/bin/mysqld.exe',
            'mysql/bin/mysql_install_db.exe', 'mysql/bin/mysql.exe', 'mysql/bin/mysqladmin.exe') |
            Where-Object { !(Test-Path -LiteralPath (Join-Path $candidate $_) -PathType Leaf) }
        if (!$missing) { return (Get-Item -LiteralPath $candidate).FullName }
    }
    throw 'Khong tim thay XAMPP. Cai XAMPP 8.0.30 (Windows 64-bit) hoac dat bien XAMPP_HOME tro den thu muc XAMPP.'
}

function Get-LocalPort {
    $listener = New-Object Net.Sockets.TcpListener -ArgumentList ([Net.IPAddress]::Loopback), 0
    try {
        $listener.Start()
        return $listener.LocalEndpoint.Port
    } finally {
        $listener.Stop()
    }
}

function New-LocalSecret {
    $bytes = New-Object byte[] 18
    $random = [Security.Cryptography.RandomNumberGenerator]::Create()
    try {
        $random.GetBytes($bytes)
        return [BitConverter]::ToString($bytes).Replace('-', '').ToLowerInvariant()
    } finally {
        $random.Dispose()
    }
}

function Start-Server($Program, $Arguments, $LogName) {
    # Start-Process joins ArgumentList, so quote each argument for paths with spaces.
    $commandLine = ($Arguments | ForEach-Object { '"' + $_ + '"' }) -join ' '
    Start-Process -FilePath $Program -ArgumentList $commandLine -WorkingDirectory $project `
        -RedirectStandardOutput (Join-Path $state "$LogName.out.log") `
        -RedirectStandardError (Join-Path $state "$LogName.err.log") -WindowStyle Hidden -PassThru
}

function Invoke-Database($Sql, $Password, [switch]$AllowFailure) {
    $mysqlOptions = @('--no-defaults', '--protocol=TCP', '--host=127.0.0.1',
        "--port=$dbPort", '--user=root', '--connect-timeout=2', '--batch',
        '--skip-column-names', '--default-character-set=utf8mb4')
    $before = $env:MYSQL_PWD
    try {
        $env:MYSQL_PWD = $Password
        # Windows PowerShell pipes native stdin using $OutputEncoding.
        $OutputEncoding = $utf8
        # A failed readiness probe writes to stderr. Handle the native exit code below.
        $ErrorActionPreference = 'Continue'
        $result = $Sql | & $mysql @mysqlOptions 2>$null
        if ($LASTEXITCODE -ne 0) {
            if ($AllowFailure) { return $null }
            throw 'MariaDB khong the thuc hien thiet lap du lieu.'
        }
        return $result
    } finally {
        $env:MYSQL_PWD = $before
    }
}

function Start-Database($Directory, $Password) {
    $script:databaseReady = $false
    $script:dbPort = Get-LocalPort
    $script:databaseProcess = Start-Server $mysqld @('--no-defaults', '--console',
        "--basedir=$(Join-Path $xampp 'mysql')", "--datadir=$Directory",
        '--bind-address=127.0.0.1', "--port=$dbPort", "--tmpdir=$state") 'mariadb'

    for ($attempt = 0; $attempt -lt 50; $attempt++) {
        if ($databaseProcess.HasExited) { throw 'MariaDB da dung khi khoi dong. Xem mariadb.err.log.' }
        $actual = Invoke-Database 'SELECT @@datadir;' $Password -AllowFailure
        if ($actual) {
            $actualPath = [IO.Path]::GetFullPath($actual.Replace('/', '\')).TrimEnd('\')
            if ($actualPath -ne $Directory) { throw 'Cong du lieu dang tro den mot MariaDB khac.' }
            $script:databaseReady = $true
            return
        }
        Start-Sleep -Milliseconds 200
    }
    throw 'Het thoi gian cho MariaDB. Xem mariadb.err.log.'
}

function Stop-Database($Password) {
    if (!$databaseProcess -or $databaseProcess.HasExited) { return }
    if ($databaseReady) {
        $before = $env:MYSQL_PWD
        try {
            $env:MYSQL_PWD = $Password
            $ErrorActionPreference = 'Continue'
            & $mysqladmin --no-defaults --protocol=TCP --host=127.0.0.1 "--port=$dbPort" `
                --user=root --connect-timeout=2 shutdown 2>$null | Out-Null
        } finally {
            $env:MYSQL_PWD = $before
        }
    }
    if (!$databaseProcess.WaitForExit(6000)) {
        Stop-Process -InputObject $databaseProcess -Force -ErrorAction SilentlyContinue
        $databaseProcess.WaitForExit()
    }
    $script:databaseReady = $false
}

function Set-SiteDatabase($Password) {
    $env:NEXA_DB_HOST = '127.0.0.1'
    $env:NEXA_DB_PORT = [string]$dbPort
    $env:NEXA_DB_NAME = 'nexa_crm'
    $env:NEXA_DB_USER = 'nexa_local'
    $env:NEXA_DB_PASS = $Password
}

function Wait-Site($Url, $Process) {
    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        if ($Process.HasExited) { throw 'May chu PHP da dung khi khoi dong. Xem php.err.log.' }
        $web = [Net.WebRequest]::Create($Url + 'api/session.php')
        $web.Proxy = $null
        $web.Timeout = 1000
        $response = $null
        try {
            $response = $web.GetResponse()
            $reader = New-Object IO.StreamReader -ArgumentList $response.GetResponseStream()
            try {
                $session = $reader.ReadToEnd() | ConvertFrom-Json
                if ($session.success -and $session.data.csrf_token) { return }
            } finally {
                $reader.Dispose()
            }
        } catch [Net.WebException] {
            # The listener may not yet be ready.
        } finally {
            if ($response) { $response.Dispose() }
        }
        Start-Sleep -Milliseconds 250
    }
    throw 'Het thoi gian cho web. Xem php.err.log.'
}

$databaseProcess = $null
$databaseReady = $false
$phpProcess = $null
$lock = $null
$pending = $null
$credentials = $null
$status = 0
try {
    $xampp = Find-Xampp
    $php = Join-Path $xampp 'php/php.exe'
    $mysql = Join-Path $xampp 'mysql/bin/mysql.exe'
    $mysqld = Join-Path $xampp 'mysql/bin/mysqld.exe'
    $mysqladmin = Join-Path $xampp 'mysql/bin/mysqladmin.exe'
    $schema = Join-Path $project 'database.sql'
    $router = Join-Path $PSScriptRoot 'dev-router.php'
    $adminSetup = Join-Path $PSScriptRoot 'create-admin.php'
    foreach ($required in @($schema, $router, $adminSetup, (Join-Path $project 'index.php'))) {
        if (!(Test-Path -LiteralPath $required -PathType Leaf)) { throw "Thieu file: $required. Hay giai nen toan bo du an." }
    }
    $phpOptions = @('-c', (Join-Path $xampp 'php/php.ini'),
        '-d', "extension_dir=$(Join-Path $xampp 'php/ext')")
    & $php @phpOptions -r 'exit(PHP_VERSION_ID >= 80000 && extension_loaded(''pdo_mysql'') && extension_loaded(''session'') ? 0 : 1);'
    if ($LASTEXITCODE -ne 0) { throw 'Can PHP 8.0 tro len voi extensions pdo_mysql va session.' }
    Add-Type -AssemblyName System.Security

    # A separate profile for each extraction path, outside Apache's document root.
    $hash = [Security.Cryptography.SHA256]::Create()
    try {
        $key = [BitConverter]::ToString($hash.ComputeHash([Text.Encoding]::UTF8.GetBytes($project.ToUpperInvariant()))).
            Replace('-', '').Substring(0, 24).ToLowerInvariant()
    } finally {
        $hash.Dispose()
    }
    $state = Join-Path (Join-Path $env:LOCALAPPDATA 'NexaCRM') $key
    $null = [IO.Directory]::CreateDirectory($state)
    try {
        $lock = [IO.File]::Open((Join-Path $state 'launch.lock'), 'OpenOrCreate', 'ReadWrite', 'None')
    } catch [IO.IOException] {
        throw 'Ban du an nay dang duoc khoi dong trong mot cua so khac.'
    }
    $installed = Join-Path $state 'data'
    $firstRun = !(Test-Path -LiteralPath $installed)

    if ($firstRun) {
        Write-Host 'Dang khoi tao du lieu rieng cho ban demo ...'
        $credentials = @{
            dbRoot = New-LocalSecret
            dbUser = New-LocalSecret
            adminPassword = 'Nexa-' + (New-LocalSecret)
        }
        $pending = Join-Path $state ('setup-' + [Guid]::NewGuid().ToString('N'))
        $null = [IO.Directory]::CreateDirectory($pending)
        $dataDir = Join-Path $pending 'mysql'
        $installDb = Join-Path $xampp 'mysql/bin/mysql_install_db.exe'
        & $installDb "--datadir=$dataDir" "--password=$($credentials.dbRoot)" --silent |
            Set-Content -LiteralPath (Join-Path $state 'mariadb-setup.log')
        if ($LASTEXITCODE -ne 0) { throw 'Khong tao duoc thu muc du lieu MariaDB rieng.' }

        Start-Database $dataDir $credentials.dbRoot
        $sql = [IO.File]::ReadAllText($schema, [Text.Encoding]::UTF8)
        $sql += "`nCREATE USER 'nexa_local'@'127.0.0.1' IDENTIFIED BY '$($credentials.dbUser)';"
        $sql += "`nGRANT SELECT, INSERT, UPDATE, DELETE ON nexa_crm.* TO 'nexa_local'@'127.0.0.1';`n"
        Invoke-Database $sql $credentials.dbRoot | Out-Null
        Set-SiteDatabase $credentials.dbUser
        $env:NEXA_ADMIN_NAME = 'Nexa Demo Admin'
        $env:NEXA_ADMIN_EMAIL = 'admin@example.test'
        $env:NEXA_ADMIN_PASSWORD = $credentials.adminPassword
        try {
            & $php @phpOptions $adminSetup
            if ($LASTEXITCODE -ne 0) { throw 'Khong tao duoc tai khoan Admin.' }
        } finally {
            Remove-Item Env:NEXA_ADMIN_PASSWORD -ErrorAction SilentlyContinue
        }

        Stop-Database $credentials.dbRoot
        # Publish the first-run database only after import and Admin setup succeed.
        @{
            dbRoot = Protect-Password $credentials.dbRoot
            dbUser = Protect-Password $credentials.dbUser
        } | ConvertTo-Json | Set-Content -LiteralPath (Join-Path $pending 'database.json') -Encoding ASCII
        [IO.Directory]::Move($pending, $installed)
        $pending = $null
        Write-Host 'Admin email: admin@example.test'
        Write-Host "Admin password: $($credentials.adminPassword)"
        Write-Host 'Hay luu lai mat khau khoi tao. Ban co the doi trong trang Tai khoan.'
    } else {
        $saved = Get-Content -LiteralPath (Join-Path $installed 'database.json') -Raw | ConvertFrom-Json
        $credentials = @{
            dbRoot = Unprotect-Password $saved.dbRoot
            dbUser = Unprotect-Password $saved.dbUser
        }
    }

    Start-Database (Join-Path $installed 'mysql') $credentials.dbRoot
    Set-SiteDatabase $credentials.dbUser
    $sessions = Join-Path $state 'sessions'
    $null = [IO.Directory]::CreateDirectory($sessions)
    $port = Get-LocalPort
    $url = "http://127.0.0.1:$port/"
    $env:NEXA_APP_URL = $url
    $phpProcess = Start-Server $php ($phpOptions + @('-d', "session.save_path=$sessions",
        '-d', 'display_errors=0', '-S', "127.0.0.1:$port", '-t', $project, $router)) 'php'
    Wait-Site $url $phpProcess

    Write-Host "URL: $url"
    Write-Host "Dang nhap: ${url}admin/login.php"
    if ($env:NEXA_LAUNCH_NO_BROWSER -ne '1') { Start-Process $url }
    Write-Host 'Giu cua so nay mo khi su dung. Nhan Enter de dung web.'
    $null = [Console]::ReadLine()
} catch {
    Write-Host "[LOI] $($_.Exception.Message)" -ForegroundColor Red
    if ($state) { Write-Host "Thu muc du lieu va log: $state" }
    $status = 1
} finally {
    if ($phpProcess -and !$phpProcess.HasExited) {
        Stop-Process -InputObject $phpProcess -Force -ErrorAction SilentlyContinue
    }
    if ($credentials) { Stop-Database $credentials.dbRoot }
    if ($pending -and (Test-Path -LiteralPath $pending -PathType Container)) {
        $resolvedPending = (Resolve-Path -LiteralPath $pending).ProviderPath
        if ((Split-Path -Parent $resolvedPending) -eq $state -and
            (Split-Path -Leaf $resolvedPending) -match '^setup-[0-9a-f]{32}$') {
            Remove-Item -LiteralPath $resolvedPending -Recurse -Force
        }
    }
    if ($lock) { $lock.Dispose() }
}
exit $status
