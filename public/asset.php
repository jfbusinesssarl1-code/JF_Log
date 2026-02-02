<?php
// Petit script pour servir en sécurité les fichiers présents dans ../assets/
// Ex: /asset.php?f=images/logo.png
$f = $_GET['f'] ?? '';
// Nettoyage basique du chemin
$f = str_replace("..", "", $f);
$f = ltrim($f, '/\\');
$base = realpath(__DIR__ . '/../assets');
$path = realpath($base . '/' . $f);
if ($path === false || strpos($path, $base) !== 0 || !is_file($path)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$types = [
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'css' => 'text/css',
    'js' => 'application/javascript'
];
$ct = $types[$ext] ?? 'application/octet-stream';
header('Content-Type: ' . $ct);
header('Cache-Control: public, max-age=86400');
readfile($path);
exit;