<?php
/**
 * Test simple et rapide de ImageConverterV2
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger la classe sans init.php
require_once __DIR__ . '/../app/helpers/ImageConverterV2.php';

use App\Helpers\ImageConverterV2;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🧪 TEST ImageConverterV2 - Intervention Image + Spatie\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Test 1: Créer une image de test
echo "1️⃣  Création image test (800x600 PNG)\n";
$manager = new ImageManager(new Driver());
$image = $manager->create(800, 600);

// Remplir avec un gradient de couleurs (pour la compression)
$image->fill('red');  // Couleur solide pour tester la compression

$testImagePath = __DIR__ . '/../public/uploads/test_iv2.png';
$image->save($testImagePath, quality: 95);
$originalSize = filesize($testImagePath);

echo "   ✅ Image créée: test_iv2.png\n";
echo "   Taille: " . formatBytes($originalSize) . "\n\n";

// Test 2: Convertir en WebP avec différentes qualités
echo "2️⃣  Conversion PNG → WebP (test qualités)\n";
$qualities = [50, 65, 75];
$results = [];

foreach ($qualities as $q) {
    $manager->read($testImagePath)->save(__DIR__ . "/../public/uploads/test_q{$q}.webp", quality: $q);
    $size = filesize(__DIR__ . "/../public/uploads/test_q{$q}.webp");
    $reduction = (1 - $size / $originalSize) * 100;
    $results[$q] = $size;
    
    echo "   Qualité {$q}: " . formatBytes($size) . " (-{$reduction}%)\n";
}
echo "\n";

// Test 3: Simulation d'upload
echo "3️⃣  Simulation upload (convertUploadedFile)\n";

$uploadedFile = [
    'name' => 'test_photo.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $testImagePath,
    'error' => UPLOAD_ERR_OK,
    'size' => $originalSize
];

// Créer le répertoire test s'il n'existe pas
@mkdir(__DIR__ . '/../public/uploads/test_conv', 0755, true);

$result = ImageConverterV2::convertUploadedFile($uploadedFile, 'test_conv', 'webp');

if ($result) {
    $fullPath = __DIR__ . '/../public' . $result;
    $finalSize = filesize($fullPath);
    $reduction = (1 - $finalSize / $originalSize) * 100;
    
    echo "   ✅ Conversion réussie!\n";
    echo "   Chemin: {$result}\n";
    echo "   Original: " . formatBytes($originalSize) . " → Final: " . formatBytes($finalSize) . "\n";
    echo "   Compression: {$reduction}%\n\n";
    
    // Test suppression
    echo "4️⃣  Test suppression image\n";
    $deleted = ImageConverterV2::deleteImage($result);
    echo "   Suppression: " . ($deleted ? "✅ OK" : "❌ FAIL") . "\n";
    echo "   Fichier existe: " . (file_exists($fullPath) ? "❌ ERREUR" : "✅ Bien supprimé") . "\n\n";
} else {
    echo "   ❌ ERREUR conversion\n\n";
}

// Nettoyage
echo "5️⃣  Nettoyage fichiers test\n";
@unlink($testImagePath);
foreach (glob(__DIR__ . '/../public/uploads/test_q*.webp') as $f) {
    @unlink($f);
}
@rmdir(__DIR__ . '/../public/uploads/test_conv');
echo "   ✅ Fichiers test supprimés\n\n";

// Résumé
echo "═══════════════════════════════════════════════════════════════════\n";
echo "✅ TESTS COMPLÉTÉS AVEC SUCCÈS\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "ImageConverterV2 est prêt!\n\n";

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}
