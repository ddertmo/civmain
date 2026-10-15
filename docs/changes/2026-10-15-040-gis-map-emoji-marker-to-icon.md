# GIS map emoji marker replaced with a proper category icon

- **Date:** 2026-10-15
- **Commit:** `HEAD` (working tree)
- **Type:** fix
- **Areas:** Laravel web admin GIS map

## Problem

In the admin GIS Map, report markers were rendered as colored circles whose inner symbol
was an emoji (`🔧`, `📋`, or `🌿`). Depending on the browser/platform font, these emojis could
render as broken glyphs or text-like artifacts (for example `OYS`) instead of recognizable
report icons, making pins hard to identify on the map.

## Cause

In `laravel/public/gis-map.html`, `createMarker()` chose an emoji based on the report
category:

```js
var emoji = isInfra ? '🔧' : (cat.toLowerCase().includes('others') ? '📋' : '🌿');
```

That emoji was then placed directly inside the `L.divIcon` circle:

```js
'font-size:15px;' +
'">' + emoji + '</div>',
```

So the marker depended on emoji font rendering rather than on the project's existing icon system.
The page already loads Material Symbols Outlined and uses it throughout the UI.

## Changes

| File | Change |
|---|---|
| `laravel/public/gis-map.html` | Replaced category emoji with Material Symbols icon names: `construction`, `assignment`, and `eco`. |
| `laravel/public/gis-map.html` | Rendered the category icon as `<span class="material-symbols-outlined">…</span>` inside the circle. |
| `laravel/public/gis-map.html` | Updated the legend text from `Emoji = category` to `Icons = category`. |

No CSS was used to hide text. The marker's status color, circle shape, border, shadow, size,
tooltip (`concern`), and click-to-detail behavior remain unchanged.

## Verification

- `python -c "from html.parser import HTMLParser..."` parsed `gis-map.html` successfully.
- Confirmed the emoji string no longer appears in `gis-map.html`.
- Confirmed the marker span uses `material-symbols-outlined` and category icons exist:
  `construction`, `assignment`, `eco`.

## Impact / how to test by hand

1. Start Laravel: `php artisan serve --host=127.0.0.1 --port=8000`.
2. Open `http://127.0.0.1:8000/gis-map.html`.
3. Confirm Infrastructure reports show `construction`, Environment reports show `eco`,
   and Others show `assignment` inside the status-colored circle.
4. Click a marker and confirm the report detail panel still opens and the tooltip works.
PY