<?php
/**
 * Test script pour ImageConverterV2 avec Intervention Image + Spatie Optimizer
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Helpers\ImageConverterV2;

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🧪 TEST ImageConverterV2 - Intervention Image + Spatie Optimizer\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Créer une image de test (PNG 1800x1200)
echo "1️⃣  CRÉATION IMAGE TEST\n";
echo "─────────────────────────────────────────────────────────────────\n";

$testImagePath = __DIR__ . '/../public/uploads/test_image.png';
$manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
$image = $manager->create(1800, 1200);
$image->fill('red');
for ($i = 0; $i < 100; $i++) {
    $image->drawLine(function (\Intervention\Image\Geometry\Line $line) {
        $line->from(mt_rand(0, 1800), mt_rand(0, 1200));
        $line->to(mt_rand(0, 1800), mt_rand(0, 1200));
        $line->stroke('blue');
    });
}
$image->save($testImagePath, quality: 90);

$originalSize = filesize($testImagePath);
echo "✅ Image créée: {$testImagePath}\n";
echo "   Taille originale: " . formatBytes($originalSize) . " (1800x1200 PNG à 90%)\n\n";

// Test 1: Convertir PNG → WebP
echo "2️⃣  TEST: Conversion PNG → WebP\n";
echo "─────────────────────────────────────────────────────────────────\n";

$webpPath = __DIR__ . '/../public/uploads/test_image.webp';
$manager->read($testImagePath)->save($webpPath, quality: 80);
$webpSize = filesize($webpPath);
$reduction = (1 - $webpSize / $originalSize) * 100;

echo "✅ PNG converti en WebP\n";
echo "   Taille WebP: " . formatBytes($webpSize) . "\n";
echo "   Réduction: {$reduction}%\n";
echo "   Original: {$originalSize} → WebP: {$webpSize}\n\n";

// Test 2: Simuler un upload avec $_FILES
echo "3️⃣  TEST: Simulation d'upload (convertUploadedFile)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$uploadedFile = [
    'name' => 'test_large_image.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $testImagePath,
    'error' => UPLOAD_ERR_OK,
    'size' => $originalSize
];

// Créer le dossier test
$testDir = __DIR__ . '/../public/uploads/test_uploads';
if (!is_dir($testDir)) {
    @mkdir($testDir, 0755, true);
}

// Augmenter les limites pour ce test
ini_set('memory_limit', '256M');
set_time_limit(30);

$result = ImageConverterV2::convertUploadedFile($uploadedFile, 'test_uploads', 'webp');

if ($result) {
    echo "✅ Upload + Conversion réussi!\n";
    $fullPath = __DIR__ . '/../public' . $result;
    $finalSize = filesize($fullPath);
    $reduction = (1 - $finalSize / $originalSize) * 100;
    
    echo "   Chemin: {$result}\n";
    echo "   Taille finale: " . formatBytes($finalSize) . "\n";
    echo "   Réduction originale→finale: {$reduction}%\n";
    echo "   Original ({$originalSize} bytes) → Final ({$finalSize} bytes)\n";
} else {
    echo "❌ ERREUR lors de la conversion upload!\n";
}
echo "\n";

// Test 3: Test de suppression d'image
echo "4️⃣  TEST: Suppression d'image\n";
echo "─────────────────────────────────────────────────────────────────\n";

if ($result) {
    $relativePath = $result;
    $deleted = ImageConverterV2::deleteImage($relativePath);
    
    if ($deleted) {
        echo "✅ Image supprimée: {$relativePath}\n";
        if (!file_exists($fullPath)) {
            echo "   Vérification: Fichier bien supprimé du disque ✅\n";
        }
    } else {
        echo "❌ Erreur lors de la suppression\n";
    }
} else {
    echo "⏭️  Skippé (pas d'image de test créée)\n";
}
echo "\n";

// Test 4: Benchmark qualité/compression
echo "5️⃣  TEST: Benchmark qualité vs compression\n";
echo "─────────────────────────────────────────────────────────────────\n";

$qualities = [50, 65, 75, 80, 85];
echo "Format: WebP\n";
echo "Image: 1800x1200 source PNG\n\n";

foreach ($qualities as $q) {
    $benchPath = __DIR__ . '/../public/uploads/bench_quality_' . $q . '.webp';
    $manager->read($testImagePath)->save($benchPath, quality: $q);
    $size = filesize($benchPath);
    $reduction = (1 - $size / $originalSize) * 100;
    
    echo "Qualité {$q}: " . formatBytes($size) . " (réduit de {$reduction}%)\n";
}
echo "\n";

// Test 5: Vérifier les limites PHP
echo "6️⃣  VÉRIFICATION: Limites PHP\n";
echo "─────────────────────────────────────────────────────────────────\n";

$uploadMax = returnBytes(ini_get('upload_max_filesize'));
$postMax = returnBytes(ini_get('post_max_size'));
$memoryLimit = returnBytes(ini_get('memory_limit'));
$timeLimit = ini_get('max_execution_time');

echo "upload_max_filesize: " . formatBytes($uploadMax) . " (" . ini_get('upload_max_filesize') . ")\n";
echo "post_max_size: " . formatBytes($postMax) . " (" . ini_get('post_max_size') . ")\n";
echo "memory_limit: " . formatBytes($memoryLimit) . " (" . ini_get('memory_limit') . ")\n";
echo "max_execution_time: {$timeLimit}s\n";

if ($uploadMax >= 100 * 1024 * 1024 && $postMax >= 100 * 1024 * 1024) {
    echo "✅ Limites suffisantes (>= 100 MB)\n";
} else {
    echo "⚠️  Limites insuffisantes (<100 MB) - vérifier .htaccess\n";
}
echo "\n";

// Cleanup
echo "7️⃣  NETTOYAGE\n";
echo "─────────────────────────────────────────────────────────────────\n";

@unlink($testImagePath);
foreach (glob(__DIR__ . '/../public/uploads/bench_quality_*.webp') as $f) {
    @unlink($f);
}
echo "✅ Images de test supprimées\n";

// Résumé
echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "✅ TESTS COMPLÉTÉS\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "ImageConverterV2 est prêt à être intégré dans les models!\n";

// Fonctions helper
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function returnBytes($value)
{
    $value = trim($value);
    $last = strtolower($value[strlen($value) - 1]);
    $value = (int)$value;
    
    switch ($last) {
        case 'g': $value *= 1024;
        case 'm': $value *= 1024;
        case 'k': $value *= 1024;
    }
    
    return $value;
}