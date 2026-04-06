<?php
// Script pour servir en sécurité les fichiers présents dans ../assets/ ou ../public/uploads/
// Ex: /asset.php?f=images/logo.png ou /asset.php?f=uploads/admin/home/image.png
$f = $_GET['f'] ?? '';
// Nettoyage basique du chemin
$f = str_replace("..", "", $f);
$f = ltrim($f, '/\\');

// Supprimer le préfixe /uploads/ s'il est présent (simplifie le nettoyage)
if (strpos($f, 'uploads/') === 0) {
    $f = substr($f, 8); // Enlever "uploads/"
}

// Déterminer le répertoire de base (assets ou uploads)
$base = null;
$path = null;

// Essayer d'abord assets/
$assetsBase = realpath(__DIR__ . '/../assets');
$assetsPath = realpath($assetsBase . '/' . $f);
if ($assetsPath !== false && strpos($assetsPath, $assetsBase) === 0 && is_file($assetsPath)) {
    $base = $assetsBase;
    $path = $assetsPath;
}

// Sinon essayer uploads/
if ($path === null) {
    $uploadsBase = realpath(__DIR__ . '/uploads');
    $uploadsPath = realpath($uploadsBase . '/' . $f);
    if ($uploadsPath !== false && strpos($uploadsPath, $uploadsBase) === 0 && is_file($uploadsPath)) {
        $base = $uploadsBase;
        $path = $uploadsPath;
    }
}

// Si le fichier n'existe pas
if ($path === null) {
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
    'webp' => 'image/webp',
    'svg' => 'image/svg+xml',
    'css' => 'text/css',
    'js' => 'application/javascript'
];
$ct = $types[$ext] ?? 'application/octet-stream';
header('Content-Type: ' . $ct);
header('Cache-Control: public, max-age=86400');
readfile($path);
exit;
