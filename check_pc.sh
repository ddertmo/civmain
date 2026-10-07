#!/usr/bin/env bash
# CivilWatch PC health check - HTTP checks only, so it runs from Android
# Termux (or any Linux/macOS terminal) against the Windows PC over the same
# Wi-Fi. The full check (services, database, git sync) runs on the PC itself
# with check_pc.ps1 / CHECK_PC.bat.
#
# Usage:
#   ./check_pc.sh                 # check 127.0.0.1 (run ON the PC)
#   ./check_pc.sh 192.168.1.14    # check your PC from Termux - use the PC's
#                                 # IPv4 address from `ipconfig` on Windows
#   CW_PC=192.168.1.14 ./check_pc.sh
#
# Everything goes through Apache on port 80. Apache listens on the LAN, so it
# is reachable from a phone; port 8000 (php artisan serve) is bound to
# 127.0.0.1 only and is therefore intentionally not checked from outside.
set -u

HOST="${1:-${CW_PC:-127.0.0.1}}"
BASE="http://${HOST}"
pass=0
fail=0

check() {
    name="$1"
    url="$2"
    want="$3"
    needle="${4:-}"

    out=$(curl -s --max-time 10 -w '\n%{http_code}' "$url" 2>/dev/null || true)
    code=$(printf '%s\n' "$out" | tail -n1)
    body=$(printf '%s\n' "$out" | sed '$d')

    if [ "$code" = "$want" ] && { [ -z "$needle" ] || printf '%s' "$body" | grep -q "$needle"; }; then
        printf 'PASS  %-32s HTTP %s\n' "$name" "$code"
        pass=$((pass + 1))
    else
        printf 'FAIL  %-32s HTTP %s\n' "$name" "$code"
        fail=$((fail + 1))
    fi
}

echo "CivilWatch PC health check - host: $HOST"
echo

check 'Website /'                 "$BASE/"                               200
check 'Admin login page'          "$BASE/admin/login"                    200
check 'API ping (Laravel + PHP)'  "$BASE/api/ping"                       200 'CivilWatch API'
check 'Community feed (database)' "$BASE/api/mobile/reports/community"   200
check 'phpMyAdmin'                "$BASE/phpmyadmin/"                    200

echo
total=$((pass + fail))
if [ "$fail" -eq 0 ]; then
    echo "RESULT: ALL $pass CHECKS PASSED"
    exit 0
fi

echo "RESULT: $fail of $total CHECKS FAILED"
echo
echo "How to fix:"
echo "  - Everything failed ..... phone and PC are not on the same Wi-Fi, or Windows"
echo "                            Firewall blocks Apache. Allow 'Apache HTTP Server'"
echo "                            in Windows Defender Firewall (inbound rule, port 80)."
echo "  - Website/admin fail .... start Apache in the XAMPP Control Panel."
echo "  - ping fail only ......... DocumentRoot is not .../laravel/public (httpd.conf)."
echo "  - feed fail only ......... start MySQL in the XAMPP Control Panel."
exit 1
