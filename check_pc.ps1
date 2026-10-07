<#
.SYNOPSIS
  CivilWatch PC health check (Windows side).

.DESCRIPTION
  Verifies everything the CivilWatch local setup needs on this machine:
    - XAMPP services (Apache, MySQL)
    - web pages served by Apache
    - Laravel API + database (through /api endpoints)
    - php artisan serve backend on port 8000 (what the Flutter app calls)
    - the public/storage symlink and report-photo CORS header
    - the database table counts
    - git sync (local HEAD vs the remote)

  Run it from the civmain checkout (double-click CHECK_PC.bat) or from this
  civilwatch_check_pc repo. Exit code 0 = all checks passed, 1 = something failed.

.EXAMPLE
  powershell -NoProfile -ExecutionPolicy Bypass -File check_pc.ps1
  powershell -NoProfile -ExecutionPolicy Bypass -File check_pc.ps1 -Repo C:\Users\me\Documents\civmain
#>
param(
    [string]$Repo = $PSScriptRoot
)

$checks = @()
function Add-Check([string]$Name, [bool]$Ok, [string]$Detail, [string]$Hint = '') {
    $script:checks += [pscustomobject]@{ Name = $Name; Ok = $Ok; Detail = $Detail; Hint = $Hint }
}

function Get-Http([string]$Url, [hashtable]$Headers = @{}) {
    try {
        $r = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 10 -Headers $Headers
        return [pscustomobject]@{ Code = [int]$r.StatusCode; Headers = $r.Headers; Body = [string]$r.Content }
    } catch {
        $resp = $_.Exception.Response
        if ($resp) { return [pscustomobject]@{ Code = [int]$resp.StatusCode; Headers = $null; Body = '' } }
        return [pscustomobject]@{ Code = 0; Headers = $null; Body = $_.Exception.Message }
    }
}

Write-Host "CivilWatch PC health check  ($(Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))"
Write-Host "Repo folder: $Repo"
Write-Host ""

# ── 1. XAMPP services ───────────────────────────────────────────────────────
$apache = @(Get-Process -Name httpd -ErrorAction SilentlyContinue)
Add-Check 'Apache (XAMPP) running' ($apache.Count -gt 0) "$($apache.Count) httpd process(es)" `
    'Open the XAMPP Control Panel (C:\xampp\xampp_control.exe) and start Apache.'

$mysqlProc = @(Get-Process -Name mysqld -ErrorAction SilentlyContinue)
Add-Check 'MySQL (XAMPP) running' ($mysqlProc.Count -gt 0) "$($mysqlProc.Count) mysqld process(es)" `
    'Open the XAMPP Control Panel (C:\xampp\xampp_control.exe) and start MySQL.'

# ── 2. Pages served by Apache (port 80 — also reachable from the LAN) ──────
$r = Get-Http 'http://localhost/'
Add-Check 'Website /' ($r.Code -eq 200) "HTTP $($r.Code)" `
    'Apache is up but not serving the app - check DocumentRoot in httpd.conf (must be .../laravel/public).'

$r = Get-Http 'http://localhost/admin/login'
Add-Check 'Admin login page' ($r.Code -eq 200) "HTTP $($r.Code)" `
    'Check laravel/storage/logs/laravel.log for the error.'

$r = Get-Http 'http://localhost/api/ping'
Add-Check 'API ping (Laravel + PHP)' (($r.Code -eq 200) -and ($r.Body -like '*CivilWatch*')) "HTTP $($r.Code)" `
    'PHP is not executing through Apache - check the Laravel log.'

$r = Get-Http 'http://localhost/api/mobile/reports/community'
Add-Check 'Community feed (database works)' ($r.Code -eq 200) "HTTP $($r.Code)" `
    'Start MySQL in XAMPP; if tables are missing, re-import laravel/civilwatch_FINAL.sql in phpMyAdmin.'

$r = Get-Http 'http://localhost/phpmyadmin/'
Add-Check 'phpMyAdmin' ($r.Code -eq 200) "HTTP $($r.Code)" `
    'phpMyAdmin is missing from C:\xampp - reinstall XAMPP or restore the phpMyAdmin folder.'

# ── 3. Backend the Flutter app talks to (port 8000) ────────────────────────
$r = Get-Http 'http://127.0.0.1:8000/api/ping'
Add-Check 'Backend :8000 (Flutter app)' ($r.Code -eq 200) "HTTP $($r.Code)" `
    'Double-click RUN_APP.bat in the civmain folder - it starts php artisan serve automatically.'

# ── 4. Storage link + photo CORS ────────────────────────────────────────────
$laravelDir = Join-Path $Repo 'laravel'
if (Test-Path $laravelDir) {
    $link = Join-Path $Repo 'laravel\public\storage'
    $linkOk = Test-Path $link
    Add-Check 'Storage link (public/storage)' $linkOk `
        $(if ($linkOk) { 'linked to storage/app/public' } else { 'MISSING' }) `
        'cd laravel && php artisan storage:link'

    $photoDir = Join-Path $Repo 'laravel\storage\app\public\report-photos'
    $photo = Get-ChildItem $photoDir -Filter *.png -File -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($photo -and $linkOk) {
        $pr = Get-Http ('http://localhost/storage/report-photos/' + $photo.Name) @{ Origin = 'http://localhost:51502' }
        $acao = ''
        if ($pr.Headers) { $acao = "$($pr.Headers['Access-Control-Allow-Origin'])" }
        $photoOk = ($pr.Code -eq 200) -and ($acao -eq '*')
        Add-Check 'Report photo + CORS' $photoOk "HTTP $($pr.Code), ACAO=$acao" `
            'Check the CORS block at the bottom of laravel/public/.htaccess.'
    } else {
        Add-Check 'Report photo + CORS' $true 'skipped - no uploaded photo yet' ''
    }
} else {
    Add-Check 'Storage link (public/storage)' $true 'skipped - no laravel\ folder here (run from civmain, or pass -Repo)' ''
    Add-Check 'Report photo + CORS'          $true 'skipped - no laravel\ folder here' ''
}

# ── 5. Database contents ────────────────────────────────────────────────────
$mysqlExe = 'C:\xampp\mysql\bin\mysql.exe'
if (Test-Path $mysqlExe) {
    $out = @(& $mysqlExe -u root -N -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='civilwatch'; SELECT COUNT(*) FROM citizens; SELECT COUNT(*) FROM citizen_reports;" 2>&1)
    $nums = @($out | ForEach-Object { "$_".Trim() } | Where-Object { $_ -match '^\d+$' })
    if ($nums.Count -ge 3) {
        $tables = [int]$nums[0]; $citizens = [int]$nums[1]; $reports = [int]$nums[2]
        Add-Check 'Database civilwatch' ($tables -ge 16) "$tables tables, $citizens citizens, $reports reports" `
            'Start MySQL; re-import laravel/civilwatch_FINAL.sql via phpMyAdmin if tables are missing (the sessions table is created by migration and survives imports).'
    } else {
        $firstErr = @($out | ForEach-Object { "$_" } | Where-Object { $_ -match 'ERROR|Can.t connect' } | Select-Object -First 1)
        Add-Check 'Database civilwatch' $false "$(if ($firstErr) { $firstErr[0] } else { 'unexpected mysql output' })" `
            'Start MySQL in the XAMPP Control Panel.'
    }
} else {
    Add-Check 'Database civilwatch' $false 'mysql.exe not found at C:\xampp\mysql\bin' 'Install XAMPP at C:\xampp.'
}

# ── 6. Git sync ─────────────────────────────────────────────────────────────
if (Test-Path (Join-Path $Repo '.git')) {
    $remotes = @(git -C $Repo remote 2>$null)
    $remote = ''
    if ($remotes -contains 'backup') { $remote = 'backup' }
    elseif ($remotes -contains 'origin') { $remote = 'origin' }

    if ($remote) {
        $dirty   = @(git -C $Repo status --short 2>$null)
        $local   = ("$(git -C $Repo rev-parse HEAD 2>$null)").Trim()
        $lsLine  = ("$(git -C $Repo ls-remote $remote main 2>$null)").Trim()
        $remoteSha = ($lsLine -split '\s+')[0]

        if ($remoteSha -and $local -and $remoteSha -eq $local) {
            if ($dirty.Count -eq 0) {
                Add-Check 'Git sync' $true "HEAD == $remote/main ($($local.Substring(0,7)))" ''
            } else {
                Add-Check 'Git sync' $false "$($dirty.Count) uncommitted change(s)" `
                    'Commit your work and push (see the changelog skill).'
            }
        } elseif ($remoteSha -and $local) {
            Add-Check 'Git sync' $false "local $($local.Substring(0,7)) != remote $($remoteSha.Substring(0,7))" `
                "git push $remote main"
        } else {
            Add-Check 'Git sync' $false 'remote not reachable (offline?)' `
                'Check the internet connection, then git push.'
        }
    } else {
        Add-Check 'Git sync' $true 'no remote configured - skipped' ''
    }
} else {
    Add-Check 'Git sync' $true 'not a git checkout - skipped' ''
}

# ── Report ──────────────────────────────────────────────────────────────────
Write-Host ''
foreach ($c in $checks) {
    $tag = if ($c.Ok) { 'PASS' } else { 'FAIL' }
    $color = if ($c.Ok) { 'Green' } else { 'Red' }
    Write-Host ('{0}  {1,-34} {2}' -f $tag, $c.Name, $c.Detail) -ForegroundColor $color
}

$failed = @($checks | Where-Object { -not $_.Ok })
Write-Host ''
if ($failed.Count -eq 0) {
    Write-Host ("RESULT: ALL {0} CHECKS PASSED" -f $checks.Count) -ForegroundColor Green
    exit 0
}

Write-Host ("RESULT: {0} of {1} CHECKS FAILED" -f $failed.Count, $checks.Count) -ForegroundColor Red
Write-Host ''
Write-Host 'How to fix:' -ForegroundColor Yellow
foreach ($f in $failed) {
    if ($f.Hint) { Write-Host ("  - {0}`n      {1}" -f $f.Name, $f.Hint) }
}
exit 1
