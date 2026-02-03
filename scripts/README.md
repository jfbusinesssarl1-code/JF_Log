test_caisse_ajax.php — usage

Purpose
- Simple integration test that performs an AJAX-style POST to `?page=caisse&action=add`, then GETs `?page=caisse&action=list_partial` to verify the new row appears and `X-Row-Count` increases.

Requirements
- PHP CLI with cURL extension
- App running and reachable (local server)

Examples
- With authentication (recommended):
  php test_caisse_ajax.php --url=http://localhost/CB-JF/public --user=caissier --pass=secret

- Without authentication (if your dev server doesn't require login):
  php test_caisse_ajax.php --url=http://localhost/CB-JF/public --no-auth

Exit codes
- 0 = OK
- >0 = failure (script prints diagnostic to STDERR)

Notes
- The script intentionally fails fast and prints server response snippets to help debugging (CSRF/login/HTML errors). Use it in CI or locally to reproduce the issue the user reported.