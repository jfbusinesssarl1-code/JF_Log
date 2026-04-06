<?php
/**
 * Script Diagnostic ImageConverter
 * Identifie les images problématiques et vérifie la configuration
 * 
 * Usage: php scripts/image_diagnostic.php
 */

require_once __DIR__ . '/../init.php';

use App\Helpers\ImageConverter;

echo "═══════════════════════════════════════════════════════════════\n";
echo "   📸 DIAGNOSTIC ImageConverter - Vérification de Configuration\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Vérifier l'extension GD
echo "1️⃣  Extension GD\n";
echo "────────────────────────────────────────────────────\n";
if (ImageConverter::isGdAvailable()) {
  echo "   ✅ GD est disponible\n";
  $gdinfo = gd_info();
  echo "   📊 Version: " . ($gdinfo['GD Version'] ?? 'Inconnue') . "\n";
} else {
  echo "   ❌ GD n'est pas disponible\n";
  echo "   ⚠️  Installez l'extension GD pour activer la conversion.\n";
}

// 2. Vérifier le support WebP
echo "\n2️⃣  Support WebP\n";
echo "────────────────────────────────────────────────────\n";
if (ImageConverter::isWebpSupported()) {
  echo "   ✅ WebP est supporté (format optimal)\n";
} else {
  echo "   ⚠️  WebP n'est pas supporté, fallback sur JPEG\n";
}

// 3. Vérifier les permissions des répertoires
echo "\n3️⃣  Permissions des Répertoires\n";
echo "────────────────────────────────────────────────────\n";
$uploadDirs = [
  'public/uploads' => 'Répertoire uploads',
  'public/uploads/admin' => 'Admin',
  'public/uploads/admin/home' => 'Carousel (home)',
  'public/uploads/admin/activities' => 'Activités',
  'public/uploads/admin/about' => 'À propos',
  'public/uploads/admin/partners' => 'Partenaires',
];

foreach ($uploadDirs as $dir => $label) {
  $fullPath = __DIR__ . '/../' . $dir;
  if (is_dir($fullPath)) {
    $writable = is_writable($fullPath) ? '✅ Accessible' : '❌ Non accessible';
    echo "   $writable: $label ($dir)\n";
  } else {
    echo "   📁 À créer: $label ($dir)\n";
  }
}

// 4. Analyser les images existantes
echo "\n4️⃣  Analyse des Images Existantes\n";
echo "────────────────────────────────────────────────────\n";

$imageDirs = [
  'public/uploads/admin/home' => 'Carousel',
  'public/uploads/admin/activities' => 'Activités',
  'public/uploads/admin/about' => 'À propos',
];

$totalImages = 0;
$problemImages = [];
$formatStats = [];

foreach ($imageDirs as $dir => $label) {
  $fullPath = __DIR__ . '/../' . $dir;
  if (!is_dir($fullPath)) {
    continue;
  }

  $files = glob($fullPath . '/*');
  $count = 0;

  foreach ($files as $file) {
    if (!is_file($file))
      continue;

    $count++;
    $totalImages++;

    // Vérifier si c'est une image valide
    $imageInfo = @getimagesize($file);
    if ($imageInfo === false) {
      $problemImages[] = [
        'file' => basename($file),
        'dir' => $label,
        'problem' => 'Fichier corrompu ou non-image'
      ];
      continue;
    }

    // Extraire le format
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!isset($formatStats[$ext])) {
      $formatStats[$ext] = 0;
    }
    $formatStats[$ext]++;

    // Vérifier la taille
    $size = filesize($file);
    if ($size > 5 * 1024 * 1024) { // > 5 MB
      $problemImages[] = [
        'file' => basename($file),
        'dir' => $label,
        'problem' => 'Image trop volumineuse (' . number_format($size / 1024 / 1024, 2) . ' MB)'
      ];
    }
  }

  echo "   📁 $label: $count image(s)\n";
}

echo "\n   📊 Statistiques par Format:\n";
arsort($formatStats);
foreach ($formatStats as $ext => $count) {
  echo "      • .$ext: $count fichier(s)\n";
}

// 5. Afficher les problèmes détectés
if (!empty($problemImages)) {
  echo "\n5️⃣  Images Problématiques Détectées\n";
  echo "────────────────────────────────────────────────────\n";
  foreach ($problemImages as $problem) {
    echo "   ⚠️  [{$problem['dir']}] {$problem['file']}\n";
    echo "       └─ {$problem['problem']}\n";
  }
} else {
  echo "\n5️⃣  Images Problématiques\n";
  echo "────────────────────────────────────────────────────\n";
  echo "   ✅ Aucun problème détecté\n";
}

// 6. Recommandations
echo "\n6️⃣  Recommandations\n";
echo "────────────────────────────────────────────────────\n";

$recommendations = [];

if (!ImageConverter::isGdAvailable()) {
  $recommendations[] = "⚠️  Installez GD pour activer la conversion d'images";
}

if (!ImageConverter::isWebpSupported()) {
  $recommendations[] = "⚠️  Activez WebP pour une meilleure compression (plus important)";
}

if ($totalImages > 0) {
  $webpCount = $formatStats['webp'] ?? 0;
  if ($webpCount === 0 && ImageConverter::isWebpSupported()) {
    $recommendations[] = "💡 Aucune image WebP détectée. Exécutez:\n      php scripts/convert_images.php public/uploads/admin webp";
  }
}

if (empty($recommendations)) {
  echo "   ✅ Configuration optimale!\n";
} else {
  foreach ($recommendations as $rec) {
    echo "   $rec\n";
  }
}

// 7. Test de conversion
if (ImageConverter::isGdAvailable()) {
  echo "\n7️⃣  Test de Conversion Rapide\n";
  echo "────────────────────────────────────────────────────\n";

  // Créer une image de test simple
  $testImage = __DIR__ . '/../public/uploads/admin/test_image.png';
  $testDir = dirname($testImage);

  if (!is_dir($testDir)) {
    @mkdir($testDir, 0755, true);
  }

  // Créer une image PNG de test 100x100
  $img = imagecreate(100, 100);
  $color = imagecolorallocate($img, 52, 73, 94);
  imagefill($img, 0, 0, $color);
  imagepng($img, $testImage);
  imagedestroy($img);

  // Tester la conversion
  $conversions = ['jpeg', 'webp'];
  foreach ($conversions as $format) {
    $result = ImageConverter::convert($testImage, $format);
    if ($result !== false) {
      echo "   ✅ Conversion vers $format réussie\n";
    } else {
      echo "   ❌ Conversion vers $format a échoué\n";
    }
  }

  // Nettoyer
  @unlink($testImage);
  $convertedFiles = glob(dirname($testImage) . '/test_image.*');
  foreach ($convertedFiles as $f) {
    @unlink($f);
  }
}

// Résumé final
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "   Diagnostic Terminé\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   Total images trouvées: $totalImages\n";
echo "   Problèmes détectés: " . count($problemImages) . "\n";
echo "\n";
echo "   Pour plus d'informations:\n";
echo "   → Voir: scripts/IMAGECONVERTER_README.md\n";
echo "   → Pour convertir en bulk: php scripts/convert_images.php\n";
echo "═══════════════════════════════════════════════════════════════\n";
