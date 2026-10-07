# What `git pull` gives you on the PC vs what you must install

- **Date:** 2026-10-07
- **Commit:** `dc8c30c`
- **Type:** docs
- **Areas:** docs / tooling

## Problem

Asked directly: *"is it done — what about graphify, do I need to download
that too?"* Nothing in the repo answered it. `docs/SESSION_CONTEXT.md`
covered the Android machine (§4) but not what actually transfers when you
`git pull backup main` on the PC, so a fresh session could not tell:

- whether the knowledge **graph** is already in the repository or has to be
  rebuilt (it is already there — 335 tracked files)
- whether the OpenCode **plugin/skills** need `npm install` (they do not)
- whether the **`graphify` CLI** is required just to *read* the graph (it is
  not — only to run `query`/`path`/`explain`/`update`)
- that the PyPI package and the CLI have **different names**
- what the newly pushed `RUN_APP`/`RUN_BACKEND`/`CHECK_PC` scripts do, and
  that they carry machine-specific hardcoded paths

## Changes

**`docs/SESSION_CONTEXT.md` — new §6b, "What `git pull` gives you vs what you
must install",** slotted between §6 (running it) and §7 (architecture facts).

- **"Comes with the repo" table** — `graphify-out/` (~335 files: `graph.json`,
  `graph.html`, `GRAPH_REPORT.md`, `wiki/` one plain-markdown article per
  community, `cache/`), `.opencode/opencode.json`, `.opencode/plugins/graphify.js`,
  both skills, and the context/guide/dump files. Nothing in this half needs a
  download.
- **"Must be installed" table** — `pip install graphifyy==0.9.76`, PHP 8.2+ /
  Composer, XAMPP, Flutter SDK.
- **The naming trap:** the PyPI distribution is **`graphifyy`** (double y); the
  importable module and the executable are both **`graphify`**. Pinned to
  `0.9.76` to match `.opencode/skills/graphify/.graphify_version`.
- **Scope of the CLI:** needed only to *run* `graphify query/path/explain/update`.
  Reading `graphify-out/wiki/index.md` and `GRAPH_REPORT.md` needs no install
  at all.
- **Node is not needed** — `.opencode/plugins/graphify.js` imports only `fs`
  and `path`, and `.opencode/package.json` is gitignored on purpose.
- **Windows bonus:** `tree_sitter_php` and the other parsers install there, so
  `laravel/*.php` could enter the graph for the first time. On Termux they
  cannot, which is why PHP has to be read directly here.
- **New row for the PC scripts** pushed in 017–021 — `RUN_BACKEND.bat`
  (`composer update`, ensure `.env`/`APP_KEY`, start `artisan serve` on 8000),
  `RUN_APP.bat` (`flutter pub upgrade`, start backend if 8000 is closed, open
  Chrome), `CHECK_PC.bat` → `check_pc.ps1` (full Windows check), and
  `check_pc.sh` (HTTP-only so it can run **from Termux** against the PC's LAN
  IP). Flags that `RUN_APP.bat` hardcodes `C:\Users\Administrator\Downloads\flutter`
  and that `RUN_APP.bat`/`check_pc.ps1` hardcode `C:\xampp`, while
  `RUN_BACKEND.bat` takes `php`/`composer` from `PATH`.

## Verification

Every claim in the new table was checked on this machine before writing it:

```
$ pip show graphifyy | grep -E "^(Name|Version)"
Name: graphifyy
Version: 0.9.76
$ cat .opencode/skills/graphify/.graphify_version
0.9.76
$ git check-ignore -v .opencode/package.json
.opencode/.gitignore:2:package.json	.opencode/package.json
$ grep -cE "^import|require\(" .opencode/plugins/graphify.js
2        # only "fs" and "path"
$ git ls-files graphify-out | wc -l
335
$ grep -nE "C:\\(xampp|Users)" RUN_APP.bat check_pc.ps1
RUN_APP.bat:8:  call "C:\Users\Administrator\Downloads\flutter\bin\flutter.bat" pub upgrade
RUN_APP.bat:11: ... Start-Process 'C:\xampp\php\php.exe' ...
check_pc.ps1:108: $mysqlExe = 'C:\xampp\mysql\bin\mysql.exe'
```

Read the three `.bat` files, `check_pc.ps1` and `check_pc.sh` in full to
describe what each does; the PowerShell script's own header confirms
`check_pc.sh` is HTTP-only so it reaches the PC from the phone.

Graph refresh after the edit:

```
$ grep -m1 "nodes ·" graphify-out/GRAPH_REPORT.md
- 2129 nodes · 2790 edges · 151 communities
$ graphify export wiki | tail -1
Wiki: 161 articles written to graphify-out/wiki/
```

The new section is queryable and its headings resolve as literal keys:

```
$ graphify query "what git pull gives you must install" --budget 8
Graph: graphify-out/graph.json (2129 nodes) | Traversal: BFS depth=2
Start: ['6b. What `git pull` gives you vs what you must install',
        'Install Android Studio (For Building APKs)',
        'layout.blade.php', 'longitude'] | 56 nodes found

NODE 6b. What `git pull` gives you vs what you must install
   [src=docs/SESSION_CONTEXT.md loc=L164
    community=CIVILWATCH — Session context]
```

**Not verified:**

- **The actual install on Windows.** No PC here, so `pip install graphifyy`
  was never run on Windows. What was verified is the package name, the pin and
  the module/CLI name as installed on Termux.
- **`graphify label`** still cannot run (no `GEMINI_API_KEY`/`GOOGLE_API_KEY`),
  so community names in `.graphify_labels.json` are stale.
- **PHP still absent from the graph** (`tree_sitter_php` unbuildable on
  Termux), so the Windows bonus is an expectation, not an observed result.

## Impact / how to test by hand

1. On the PC, after `git pull backup main`: `ls graphify-out/` should show the
   graph immediately — no graphify install required to read it.
2. `pip install graphifyy==0.9.76`, then `graphify --version` should resolve.
3. OpenCode on the PC should load the plugin with no `npm install` (check that
   `.opencode/node_modules` is never created).
4. `check_pc.ps1 -Repo <path>` — confirm it does not assume `C:\Users\Administrator`.
