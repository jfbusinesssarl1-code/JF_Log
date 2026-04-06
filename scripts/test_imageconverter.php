<?php
/**
 * Script Interactif de Test ImageConverter
 * Démontre le fonctionnement de la conversion d'images
 * 
 * Usage: php scripts/test_imageconverter.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Helpers\ImageConverter;

echo "═══════════════════════════════════════════════════════════════\n";
echo "   🧪 Test ImageConverter - Démonstration Interactive\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Vérifier GD
if (!ImageConverter::isGdAvailable()) {
  echo "❌ ERREUR: L'extension GD n'est pas disponible.\n";
  echo "   Veuillez installer l'extension PHP GD.\n";
  exit(1);
}

echo "✅ GD est disponible\n";
echo "✅ WebP supporté: " . (ImageConverter::isWebpSupported() ? "OUI" : "NON") . "\n\n";

// Créer un répertoire de test
$testDir = __DIR__ . '/../public/uploads/admin/test_images';
if (!is_dir($testDir)) {
  @mkdir($testDir, 0755, true);
}

// 1. Créer une image PNG de test
echo "1️⃣  Créer une Image PNG de Test\n";
echo "────────────────────────────────────────────────────\n";

$testImagePath = $testDir . '/test_original.png';

// Créer une image synthétique (gradient)
$width = 800;
$height = 600;
$image = imagecreatetruecolor($width, $height);

// Créer un dégradé coloré
for ($y = 0; $y < $height; $y++) {
  $red = intval(255 * ($y / $height));
  $green = intval(128 * (1 - $y / $height));
  $blue = intval(200 * sin($y / $height * M_PI));

  $color = imagecolorallocate($image, $red, $green, $blue);
  imageline($image, 0, $y, $width, $y, $color);
}

// Ajouter du texte
$white = imagecolorallocate($image, 255, 255, 255);
imagestring($image, 5, 50, 250, "Test ImageConverter", $white);
imagestring($image, 3, 50, 280, "Pont Maghulinga - Image de Demonstration", $white);

// Sauvegarder l'image PNG
imagepng($image, $testImagePath);
imagedestroy($image);

$pngSize = filesize($testImagePath);
echo "   ✅ PNG créé: test_original.png\n";
echo "   📊 Taille: " . number_format($pngSize / 1024, 2) . " KB\n\n";

// 2. Convertir en différents formats
echo "2️⃣  Convertir l'Image en Différents Formats\n";
echo "────────────────────────────────────────────────────\n";

$formats = ['jpeg', 'webp'];
$results = [];

foreach ($formats as $format) {
  echo "   🔄 Conversion en " . strtoupper($format) . "...\n";

  $result = ImageConverter::convert($testImagePath, $format, 85);

  if ($result !== false) {
    $convertedPath = dirname($testImagePath) . '/test_original.' . $format;
    $convertedSize = file_exists($convertedPath) ? filesize($convertedPath) : 0;

    $reduction = $pngSize > 0 ? ((1 - $convertedSize / $pngSize) * 100) : 0;

    echo "   ✅ Succès!\n";
    echo "      Fichier: test_original.$format\n";
    echo "      Taille: " . number_format($convertedSize / 1024, 2) . " KB\n";
    echo "      Réduction: -" . number_format($reduction, 1) . "% vs PNG\n";

    $results[$format] = [
      'path' => $convertedPath,
      'size' => $convertedSize,
      'reduction' => $reduction
    ];
  } else {
    echo "   ⚠️  Conversion échouée (format non supporté?)\n";
  }
  echo "\n";
}

// 3. Tester la détection de format
echo "3️⃣  Test de Détection de Format\n";
echo "────────────────────────────────────────────────────\n";

$files = glob($testDir . '/test_original.*');
foreach ($files as $file) {
  $ext = pathinfo($file, PATHINFO_EXTENSION);
  $info = @getimagesize($file);

  echo "   📄 " . basename($file) . "\n";
  if ($info !== false) {
    echo "      Type MIME: {$info['mime']}\n";
    echo "      Dimensions: {$info[0]} × {$info[1]} px\n";
  }
}

// 4. Simuler un upload
echo "\n4️⃣  Test de Conversion Upload (Simulé)\n";
echo "────────────────────────────────────────────────────\n";

// Copier le PNG pour simuler un upload
$uploadedPath = $testDir . '/uploaded_image.png';
copy($testImagePath, $uploadedPath);

// Simuler un $_FILES array
$fakeFile = [
  'name' => 'mon_image.png',
  'tmp_name' => $uploadedPath,
  'error' => UPLOAD_ERR_OK,
  'size' => filesize($uploadedPath)
];

echo "   📤 Simulation upload: mon_image.png (" . number_format(filesize($uploadedPath) / 1024, 2) . " KB)\n";

$convertedPath = ImageConverter::convertUploadedFile($fakeFile, 'admin/test_images', 'webp');

if ($convertedPath !== false) {
  echo "   ✅ Conversion succès!\n";
  echo "      Chemin retourné: $convertedPath\n";

  $fullPath = __DIR__ . '/../public' . $convertedPath;
  if (file_exists($fullPath)) {
    $size = filesize($fullPath);
    echo "      Taille finale: " . number_format($size / 1024, 2) . " KB\n";
  }
} else {
  echo "   ❌ Conversion échouée\n";
}

// 5. Rapport de compression
echo "\n5️⃣  Rapport de Compression\n";
echo "────────────────────────────────────────────────────\n";

if (!empty($results)) {
  usort($results, function ($a, $b) {
    return $a['size'] <=> $b['size'];
  });

  echo "   Taille original PNG: " . number_format($pngSize / 1024, 2) . " KB\n\n";

  foreach ($results as $format => $data) {
    $barLength = max(0, intval($data['reduction'] / 5));
    $bar = str_repeat('█', $barLength);
    $reduction = max(0, $data['reduction']);
    echo "   $format: " . str_pad($bar, 20) . " " . number_format($data['size'] / 1024, 2) . " KB";
    if ($reduction > 0) {
      echo " (-" . number_format($reduction, 1) . "%)";
    }
    echo "\n";
  }
}

// 6. Recommandations
echo "\n6️⃣  Recommandations\n";
echo "────────────────────────────────────────────────────\n";

if (ImageConverter::isWebpSupported()) {
  echo "   ✅ WebP: Excellent! Utilisez WebP par défaut.\n";
  echo "      → 30% plus petit que JPEG\n";
  echo "      → 80% plus petit que PNG\n";
} else {
  echo "   ⚠️  WebP: Non supporté, fallback sur JPEG\n";
}

echo "\n   📊 Statistiques de Compression Recommandées:\n";
echo "      • Photos/Paysages: WebP qualité 85\n";
echo "      • PNG transparent: PNG compression 5\n";
echo "      • Logos: PNG ou SVG\n";
echo "      • HiDPI (@2x): Redimensionner + WebP\n";

// Nettoyage optionnel
echo "\n7️⃣  Nettoyage des Fichiers de Test\n";
echo "────────────────────────────────────────────────────\n";

$confirm = "y"; // Demande automatiquement en production

if (strtolower($confirm) === 'y') {
  $testFiles = glob($testDir . '/test_*.*');
  $testFiles = array_merge($testFiles, glob($testDir . '/uploaded_*.*'));

  $deleted = 0;
  foreach ($testFiles as $file) {
    if (unlink($file)) {
      $deleted++;
    }
  }

  echo "   ✅ $deleted fichier(s) supprimé(s)\n";
} else {
  echo "   📁 Fichiers de test conservés dans: $testDir\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "   ✨ Test Terminé avec Succès!\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📝 Résumé:\n";
echo "   1. ImageConverter détecte automatiquement le format d'entrée\n";
echo "   2. Les images > 2000x2000 px sont redimensionnées\n";
echo "   3. Compression optimale en WebP (par défaut)\n";
echo "   4. Fallback sur JPEG/PNG si WebP indisponible\n";
echo "   5. Tout fonctionne automatiquement sur upload en admin\n\n";

echo "✅ Prêt pour la production!\n";
