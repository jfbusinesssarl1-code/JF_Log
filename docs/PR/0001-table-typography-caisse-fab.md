# PR: UI — Reduce table typography & stabilize Caisse FAB

## Summary
- Reduce global table font-size for better density (screen & print).
- Fix FAB overlap on `caisse` (smaller, elevated, CSS-driven) and correct typo.
- Add defensive dedup for optimistic row append to avoid duplicate rows on fast responses.
- Add before/after screenshots (SVG placeholders). 

## Files changed
- `assets/css/custom.css` — global table font-size + `.btn-fab-caisse` rule
- `app/views/stock.php` — adjusted stock table size to match new baseline
- `app/views/caisse.php` — FAB markup fix, optimistic-row dedup
- `docs/screenshots/caisse-before.svg`, `docs/screenshots/caisse-after.svg` — placeholders

## Screenshots
- docs/screenshots/caisse-before.svg (before)
- docs/screenshots/caisse-after.svg (after)

## How to test (QA)
1. Open `?page=caisse` (desktop & mobile widths). Verify table row density is smaller and consistent across pages (journal, stock, grandlivre).
2. Verify FAB no longer overlaps table content and remains clickable; CSS keeps a safe padding.
3. Add an operation quickly twice — confirm no duplicate optimistic row appears and that the server row replaces the optimistic one.
4. Print view (`Exporter PDF (imprimer)`) and verify table remains legible.

## Notes for maintainer
- Replace SVG placeholders with real screenshots from the user's environment (DevTools -> Capture full size). Commit them to `docs/screenshots/`.
- If you want a more compact UI, suggest values: screen `0.80rem`, mobile `0.72rem`.

---

Closes: (manual) add issue/ID if applicable
CC: @maintainer
