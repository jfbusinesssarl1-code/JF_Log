<?php
// scripts/test_caisse_ajax.php
// Usage: php test_caisse_ajax.php --url=http://localhost/CB.JF/public --user=caissier --pass=secret
// Options:
//   --url    Base URL where the app is reachable (required)
//   --user   Username to login (optional if --no-auth)
//   --pass   Password for login
//   --no-auth  Skip login and run requests without session
//
// Exit codes: 0=ok, 1=failed

$options = getopt('', ['url:', 'user::', 'pass::', 'no-auth']);
if (empty($options['url'])) {
    fwrite(STDERR, "ERROR: --url is required (example: --url=http://localhost/CB-JF/public)\n");
    exit(2);
}
$base = rtrim($options['url'], "/");
$useAuth = !isset($options['no-auth']);
$user = $options['user'] ?? null;
$pass = $options['pass'] ?? null;

function curl_init_opts($url, $cookieJar)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CBJF-test/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    return $ch;
}

$cookieJar = sys_get_temp_dir() . '/cbjf_test_cookies_' . uniqid();

// optional login
if ($useAuth && $user) {
    echo "Logging in as $user...\n";
    $loginUrl = $base . '/?page=login&action=login';
    $ch = curl_init_opts($loginUrl, $cookieJar);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => $user, 'password' => $pass ?? '', 'csrf_token' => '']));
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 400) {
        fwrite(STDERR, "Login failed (HTTP $code). Response snippet:\n" . substr( (string)$res, 0, 200 ) . "\n");
        exit(3);
    }
    echo "Login HTTP/$code ok (cookies saved).\n";
}

// 1) GET current list_partial to obtain X-Row-Count
$listUrl = $base . '/?page=caisse&action=list_partial';
$ch = curl_init_opts($listUrl, $cookieJar);
$res = curl_exec($ch);
$info = curl_getinfo($ch);
$code = $info['http_code'] ?? 0;
$ctype = $info['content_type'] ?? '';
if ($code !== 200) {
    fwrite(STDERR, "Failed to fetch list_partial (HTTP $code).\nResponse:\n" . substr((string)$res,0,1000) . "\n");
    exit(4);
}
$headers = [];
// Try to extract X-Row-Count from response headers via a second request that reads headers
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
$head = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);
$xRowCount = null;
if (preg_match('/X-Row-Count:\s*(\d+)/i', $head, $m)) {
    $xRowCount = (int)$m[1];
}
if ($xRowCount === null) {
    echo "Warning: X-Row-Count header not present. Falling back to counting <tr> elements.\n";
    preg_match_all('#<tr[^>]*>#i', $res, $m);
    $xRowCount = count($m[0]);
}
$beforeCount = $xRowCount;
echo "Before count: $beforeCount\n";

// 2) POST an AJAX add to caisse
$addUrl = $base . '/?page=caisse&action=add';
$payload = [
    'csrf_token' => '',
    'date' => date('Y-m-d'),
    'type' => 'entree',
    'numero_bon_manuscrit' => 'TST-' . rand(1000,9999),
    'operateur' => 'test-suite',
    'libelle' => 'Test automatique ' . date('H:i:s'),
    'montant' => '12.34'
];
$ch = curl_init_opts($addUrl, $cookieJar);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
$res = curl_exec($ch);
$info = curl_getinfo($ch);
$code = $info['http_code'] ?? 0;
$ctype = $info['content_type'] ?? '';
if ($code < 200 || $code >= 300) {
    fwrite(STDERR, "Add operation returned HTTP $code. Response:\n" . substr((string)$res,0,2000) . "\n");
    exit(5);
}
if (strpos((string)$ctype, 'application/json') === false) {
    fwrite(STDERR, "Add returned non-JSON (content-type=$ctype). Response snippet:\n" . substr((string)$res,0,2000) . "\n");
    exit(6);
}
$body = json_decode($res, true);
if (!$body || empty($body['success'])) {
    fwrite(STDERR, "Add did not return success JSON. Body:\n" . substr((string)$res,0,2000) . "\n");
    exit(7);
}
$inserted = $body['item'] ?? null;
if (!$inserted) {
    fwrite(STDERR, "Add succeeded but no item returned in JSON.\n");
    exit(8);
}
echo "Add returned success; inserted id: " . ($inserted['_id'] ?? '<no-id>') . "\n";

// 3) Re-fetch list_partial and assert count increased and row present
$ch = curl_init_opts($listUrl, $cookieJar);
$res2 = curl_exec($ch);
$info2 = curl_getinfo($ch);
$head = '';
if (isset($info2['request_header'])) $head = $info2['request_header'];
// get headers via second request
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
$headResp = curl_exec($ch);
curl_close($ch);
$newCount = null;
if (preg_match('/X-Row-Count:\s*(\d+)/i', $headResp, $m)) {
    $newCount = (int)$m[1];
}
if ($newCount === null) {
    preg_match_all('#<tr[^>]*>#i', $res2, $m);
    $newCount = count($m[0]);
}
echo "After count: $newCount\n";
if ($newCount <= $beforeCount) {
    fwrite(STDERR, "FAIL: row count did not increase (before=$beforeCount after=$newCount). Response snippet:\n" . substr((string)$res2,0,800) . "\n");
    exit(9);
}
// Look for the unique libelle we created
$libFragment = preg_quote($payload['libelle'], '#');
if (!preg_match('#' . $libFragment . '#i', $res2)) {
    fwrite(STDERR, "FAIL: newly inserted row not found in list_partial HTML.\nResponse snippet:\n" . substr((string)$res2,0,1200) . "\n");
    exit(10);
}

echo "OK: Add + partial refresh verified (before=$beforeCount after=$newCount).\n";
exit(0);
