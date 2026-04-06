<?php
/**
 * Script Diagnostic Spécifique - Pont Maghulinga
 * Recherche et analyse l'image du pont Maghulinga et ses problèmes
 * 
 * Usage: php scripts/debug_maghulinga_image.php
 */

require_once __DIR__ . '/../init.php';

use App\Models\ActivityModel;
use App\Helpers\ImageConverter;
use App\Helpers\AssetHelper;

echo "═══════════════════════════════════════════════════════════════\n";
echo "   🌉 Diagnostic: Pont Maghulinga - Image non affichée\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Récupérer l'activité depuis la base de données
echo "1️⃣  Recherche de l'activité 'Pont Maghulinga'\n";
echo "────────────────────────────────────────────────────\n";

$activityModel = new ActivityModel();
$activities = $activityModel->getAll();

$maghulinga = null;
foreach ($activities as $activity) {
  if (stripos($activity['title'] ?? '', 'maghulinga') !== false) {
    $maghulinga = $activity;
    break;
  }
}

if ($maghulinga === null) {
  echo "   ❌ L'activité 'Pont Maghulinga' n'a pas été trouvée.\n";
  echo "   📝 Veuillez vérifier l'orthographe ou créer l'activité.\n";
  exit(1);
}

echo "   ✅ Activité trouvée: " . htmlspecialchars($maghulinga['title']) . "\n";
echo "   📅 Date: " . ($maghulinga['date'] ?? 'N/A') . "\n";
echo "   📝 Statut: " . ($maghulinga['status'] ?? 'N/A') . "\n";

// 2. Analyser l'image
echo "\n2️⃣  Analyse du Chemin de l'Image\n";
echo "────────────────────────────────────────────────────\n";

$imagePath = $maghulinga['image'] ?? null;
if (empty($imagePath)) {
  echo "   ⚠️  Aucune image n'est associée à cette activité.\n";
  echo "   💡 Solution: Uploadez une image dans l'admin.\n";
  exit(1);
}

echo "   📄 Chemin stocké: " . htmlspecialchars($imagePath) . "\n";

// 3. Vérifier le fichier physique
echo "\n3️⃣  Vérification du Fichier Physique\n";
echo "────────────────────────────────────────────────────\n";

// Extraire le chemin sans le leading /uploads
$relativePath = str_replace('/uploads/', '', $imagePath);
$fullPath = __DIR__ . '/../public/uploads/' . $relativePath;

echo "   🔍 Chemin absolu: " . $fullPath . "\n";

if (file_exists($fullPath)) {
  echo "   ✅ Fichier existe\n";

  $fileSize = filesize($fullPath);
  echo "   📊 Taille: " . number_format($fileSize / 1024, 2) . " KB\n";

  $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
  echo "   📋 Extension: ." . $ext . "\n";

  // Vérifier si c'est une image valide
  $imageInfo = @getimagesize($fullPath);
  if ($imageInfo !== false) {
    echo "   ✅ Format image valide\n";
    echo "   🖼️  Dimensions: " . $imageInfo[0] . " × " . $imageInfo[1] . " px\n";
    echo "   🎨 Type MIME: " . ($imageInfo['mime'] ?? 'Inconnu') . "\n";
  } else {
    echo "   ❌ Le fichier n'est pas une image valide!\n";
    echo "   💡 Le fichier est corrompu ou au mauvais format.\n";

    // Proposer une conversion
    echo "\n   🔄 Tentative de conversion...\n";
    $convertResult = ImageConverter::convert($fullPath, 'jpeg', 90);
    if ($convertResult !== false) {
      echo "   ✅ Conversion réussie: " . $convertResult . "\n";
    } else {
      echo "   ❌ La conversion a également échoué.\n";
      echo "   💡 Veuillez réuploader l'image en admin.\n";
    }
  }

  // Vérifier les permissions
  echo "\n   🔐 Permissions:\n";
  $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
  echo "   📁 Actuels: " . $perms . " ";
  if (is_readable($fullPath)) {
    echo "✅ Lisible\n";
  } else {
    echo "❌ Non lisible\n";
    echo "   💡 Exécutez: chmod 644 $fullPath\n";
  }

} else {
  echo "   ❌ Fichier introuvable!\n";
  echo "   🔍 Emplacements vérifiés:\n";
  echo "      • $fullPath\n";

  // Rechercher le fichier dans les uploads
  echo "\n   🔎 Recherche dans public/uploads...\n";
  $uploadsDir = __DIR__ . '/../public/uploads';
  if (is_dir($uploadsDir)) {
    $files = glob($uploadsDir . '/**/*', GLOB_RECURSIVE);
    $found = false;
    foreach ($files as $file) {
      if (stripos(basename($file), 'maghulinga') !== false) {
        echo "   📍 Trouvé: " . str_replace(__DIR__ . '/../public', '', $file) . "\n";
        $found = true;
      }
    }

    if (!$found) {
      echo "   ❌ Aucun fichier contenant 'maghulinga' trouvé.\n";
    }
  }
}

// 4. Tester l'affichage
echo "\n4️⃣  Test d'Affichage\n";
echo "────────────────────────────────────────────────────\n";

$assetUrl = AssetHelper::url($imagePath);
echo "   🌐 URL générée par AssetHelper: $assetUrl\n";

// Vérifier si asset.php peut servir le fichier
if (file_exists($fullPath)) {
  $testUrl = 'http://localhost' . $assetUrl;
  echo "   🔗 URL complète: $testUrl\n";

  // Vérifier si asset.php est opérationnel
  $assetPhpPath = __DIR__ . '/../public/asset.php';
  if (file_exists($assetPhpPath)) {
    echo "   ✅ asset.php existe\n";
  } else {
    echo "   ❌ asset.php n'existe pas!\n";
  }
}

// 5. Recommandations
echo "\n5️⃣  Recommandations et Actions\n";
echo "────────────────────────────────────────────────────\n";

$actions = [];

if (empty($imagePath)) {
  $actions[] = "1️⃣  Aller à Admin > Activités\n      Modifier l'activité 'Pont Maghulinga'\n      Uploader une image valide";
}

if (!file_exists($fullPath) && !empty($imagePath)) {
  $actions[] = "2️⃣  Le fichier '$imagePath' est manquant.\n      Réuploadez l'image via l'admin.";
}

if (file_exists($fullPath)) {
  $imageInfo = @getimagesize($fullPath);
  if ($imageInfo === false) {
    $actions[] = "3️⃣  Le fichier '$imagePath' est corrompu.\n      Réuploadez une nouvelle image.";
  } elseif ($fileSize > 5 * 1024 * 1024) {
    $actions[] = "4️⃣  L'image est très volumineuse (" . number_format($fileSize / 1024 / 1024, 2) . " MB).\n      Convertissez-la: php scripts/convert_images.php public/uploads/admin/activities webp";
  }
}

if (empty($actions)) {
  echo "   ✅ L'image devrait s'afficher correctement!\n";
  echo "   💡 Si elle n'apparaît toujours pas:\n";
  echo "      • Videz le cache du navigateur (Ctrl+F5)\n";
  echo "      • Vérifiez la console du navigateur (F12 > Console)\n";
  echo "      • Vérifiez les logs serveur (error_log)\n";
} else {
  foreach ($actions as $i => $action) {
    echo "   " . ($i + 1) . ". " . str_replace("\n      ", "\n      ", $action) . "\n\n";
  }
}

// 6. Vérifier le rendu HTML
echo "\n6️⃣  Vérification du Rendu HTML\n";
echo "────────────────────────────────────────────────────\n";

$htmlImageTag = '<img src="' . htmlspecialchars($assetUrl) . '" alt="' . htmlspecialchars($maghulinga['title']) . '">';
echo "   Balise HTML générée:\n";
echo "   " . $htmlImageTag . "\n";

// 7. Log de débogage
echo "\n7️⃣  Données Complètes (Debug)\n";
echo "────────────────────────────────────────────────────\n";
echo json_encode([
  'title' => $maghulinga['title'] ?? null,
  'image' => $maghulinga['image'] ?? null,
  'status' => $maghulinga['status'] ?? null,
  'date' => $maghulinga['date'] ?? null,
  'id' => (string) ($maghulinga['_id'] ?? 'N/A')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "   Diagnostic Terminé\n";
echo "═══════════════════════════════════════════════════════════════\n";
