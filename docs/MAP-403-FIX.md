# Map tile 403 fix — Barangay Lower General Luna

## Problem

OpenStreetMap tiles showed `403 Access blocked` because the app sent `Referrer-Policy: same-origin`. This prevents the browser from identifying the localhost app when requesting tiles from a different origin.

## Fix

- `src/bootstrap.php`: use `strict-origin-when-cross-origin`, which only shares the website origin with the tile provider.
- `public/assets/js/app.js`: explicitly set the Leaflet tile referrer policy.
- The map now starts on the **Saved street map**, which works without network access or a tile service account. Your red area boundary, complaint pins, and admin-entered locations work normally.
- The map dropdown keeps **Online street map** and **Satellite** as optional views.
- Removed the no-key CARTO tile fallback, which may require an API key.

## Installation

### Recommended: small patch

Copy `src/bootstrap.php` and `public/assets/js/app.js` into the matching paths under your current `C:\xampp\htdocs\LOWER-GENERAL-LUNA-V3-UPDATED` project.
Do not replace your database, local configuration or uploaded photos.
Restart Apache if needed and hard-refresh the site with Ctrl+F5. No SQL import is necessary.

If **Online street map** still shows access blocked, the volunteer OSM server may have limited your traffic or browser privacy settings may strip Referer (for example, Brave Shields). Continue using **Saved street map** for the demo. A production deployment should use a tile provider with an appropriate plan/service agreement. Don't bypass tile provider access controls.

## Source

OpenStreetMap Foundation tile usage policy: https://operations.osmfoundation.org/policies/tiles/
Blocked tile help: https://wiki.openstreetmap.org/wiki/Blocked_tiles

No live external tile requests or database workflows were tested in this environment.
