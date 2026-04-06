<?php
/**
 * Diagnostic Simple - Images Maghulinga
 * Charge les autoloaders de composer correctement
 */

// Charger les autoloaders
require_once __DIR__ . '/../vendor/autoload.php';

// Configuration directe
define('APP_BASE', realpath(__DIR__ . '/..'));
define('MONGO_URI', $_ENV['MONGO_URI'] ?? 'mongodb://localhost:27017');
define('MONGO_DB', $_ENV['MONGO_DB'] ?? 'cb_jf');

use MongoDB\Client;
use MongoDB\BSON\Regex;

try {
  echo "═══════════════════════════════════════════════════════════════\n";
  echo "   🌉 Diagnostic: Images Activités (Pont Maghulinga)\n";
  echo "═══════════════════════════════════════════════════════════════\n\n";

  // Connexion MongoDB
  $client = new Client(MONGO_URI);
  $db = $client->selectDatabase(MONGO_DB);
  $collection = $db->selectCollection('activities');

  echo "1️⃣  Recherche dans MongoDB\n";
  echo "────────────────────────────────────────────────────\n";
  echo "   URI: " . MONGO_URI . "\n";
  echo "   DB:  " . MONGO_DB . "\n";
  echo "   Collection: activities\n\n";

  // Chercher toutes les activités
  $activities = $collection->find();
  $count = 0;
  $maghulinga = null;

  echo "2️⃣  Activités Trouvées\n";
  echo "────────────────────────────────────────────────────\n";

  foreach ($activities as $activity) {
    $count++;
    $title = $activity['title'] ?? '(sans titre)';
    $status = ucfirst($activity['status'] ?? 'inconnue');
    echo "   $count. $title\n";
    echo "      Statut: $status\n";

    if (!empty($activity['image'])) {
      echo "      Image: " . htmlspecialchars($activity['image']) . "\n";
    } else {
      echo "      Image: ❌ Aucune\n";
    }

    // Checker si c'est Maghulinga
    if (stripos($title, 'maghulinga') !== false) {
      $maghulinga = $activity;
    }
    echo "\n";
  }

  if ($count === 0) {
    echo "   ⚠️  Aucune activité trouvée dans la base de données.\n";
    exit(0);
  }

  // Analyser Maghulinga s'il existe
  if ($maghulinga !== null) {
    echo "3️⃣  Analyse - Pont Maghulinga\n";
    echo "────────────────────────────────────────────────────\n";

    if (empty($maghulinga['image'])) {
      echo "   ❌ PROBLÈME: Aucune image stockée!\n";
      echo "   💡 Solution: Uploadez une image via Admin > Activités\n";
    } else {
      $image = $maghulinga['image'];
      echo "   ✅ Image trouvée: $image\n";

      // Vérifier le fichier
      $relativePath = str_replace('/uploads/', '', $image);
      $filePath = APP_BASE . '/public/uploads/' . $relativePath;

      echo "\n4️⃣  Vérification du Fichier\n";
      echo "────────────────────────────────────────────────────\n";
      echo "   Chemin absolu: $filePath\n";

      if (file_exists($filePath)) {
        echo "   ✅ Fichier existe\n";

        $size = filesize($filePath);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        echo "   📊 Taille: " . number_format($size / 1024, 2) . " KB\n";
        echo "   📄 Extension: .$ext\n";

        $imgInfo = @getimagesize($filePath);
        if ($imgInfo !== false) {
          echo "   ✅ Image valide\n";
          echo "   🖼️  Dimensions: {$imgInfo[0]} × {$imgInfo[1]} px\n";
          echo "   🎨 Type MIME: {$imgInfo['mime']}\n";

          if ($size > 5 * 1024 * 1024) {
            echo "   ⚠️  Image volumineuse - considérez la conversion en WebP\n";
          }
        } else {
          echo "   ❌ Format corrompu ou invalide\n";
        }
      } else {
        echo "   ❌ Fichier introuvable!\n";
        echo "   💡 Uploadez une nouvelle image ou vérifiez le chemin\n";
      }
    }
  } else {
    echo "3️⃣  Pont Maghulinga\n";
    echo "────────────────────────────────────────────────────\n";
    echo "   ❌ 'Pont Maghulinga' non trouvé parmi les activités.\n";
    echo "   💡 Créez cette activité via Admin > Activités et uploadez une image.\n";
  }

} catch (\Exception $e) {
  echo "❌ Erreur: " . $e->getMessage() . "\n";
  echo "Trace:\n" . $e->getTraceAsString() . "\n";
  exit(1);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "   Diagnostic Terminé\n";
echo "═══════════════════════════════════════════════════════════════\n";
