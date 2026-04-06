<?php
/**
 * Script de Diagnostic Simple - Sans Dépendance init.php
 * Vérifie les images et MongoDB directement
 */

// Configuration MongoDB
$mongoUri = 'mongodb://localhost:27017';
$databaseName = 'cb_jf';

try {
  // Connexion MongoDB
  $client = new MongoDB\Client($mongoUri);
  $db = $client->selectDatabase($databaseName);
  $activities = $db->selectCollection('activities');

  echo "═══════════════════════════════════════════════════════════════\n";
  echo "   🌉 Diagnostic: Pont Maghulinga\n";
  echo "═══════════════════════════════════════════════════════════════\n\n";

  echo "1️⃣  Recherche de l'Activité 'Maghulinga'\n";
  echo "────────────────────────────────────────────────────\n";

  // Chercher l'activité
  $activity = $activities->findOne(
    ['title' => new \MongoDB\BSON\Regex('maghulinga', 'i')]
  );

  if ($activity === null) {
    echo "   ❌ L'activité n'a pas été trouvée\n";
    echo "\n2️⃣  Toutes les Activités Disponibles:\n";
    echo "────────────────────────────────────────────────────\n";

    $allActivities = $activities->find();
    foreach ($allActivities as $act) {
      echo "   • " . ($act['title'] ?? 'Sans titre') . "\n";
      if (!empty($act['image'])) {
        echo "     → Image: " . $act['image'] . "\n";
      }
    }

    echo "\n   💡 Solution: Créez une activité 'Pont Maghulinga' via l'admin\n";
    exit(0);
  }

  echo "   ✅ Activité trouvée\n";
  echo "   📋 Titre: " . htmlspecialchars($activity['title']) . "\n";
  echo "   📅 Date: " . ($activity['date'] ?? 'N/A') . "\n";
  echo "   📝 Statut: " . ($activity['status'] ?? 'N/A') . "\n";
  echo "   📄 Image: " . ($activity['image'] ?? '❌ Aucune image') . "\n";

  // Analyser l'image
  if (empty($activity['image'])) {
    echo "\n   ❌ Aucune image n'est stockée pour cette activité!\n";
    echo "   💡 Solution: Uploadez une image via Admin > Activités\n";
    exit(0);
  }

  echo "\n2️⃣  Vérification du Fichier\n";
  echo "────────────────────────────────────────────────────\n";

  $imagePath = $activity['image'];
  $relativePath = str_replace('/uploads/', '', $imagePath);
  $fullPath = __DIR__ . '/public/uploads/' . $relativePath;

  echo "   Chemin: " . htmlspecialchars($imagePath) . "\n";
  echo "   Fichier local: " . $fullPath . "\n";

  if (file_exists($fullPath)) {
    echo "   ✅ Fichier existe\n";

    $size = filesize($fullPath);
    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    echo "   📊 Taille: " . number_format($size / 1024, 2) . " KB\n";
    echo "   📋 Format: ." . $ext . "\n";

    // Vérifier si c'est une image valide
    $info = @getimagesize($fullPath);
    if ($info !== false) {
      echo "   ✅ Image valide (" . $info[0] . " × " . $info[1] . " px)\n";
      echo "      Type: {$info['mime']}\n";
    } else {
      echo "   ❌ Le fichier n'est pas une image valide (corrompu)!\n";
    }
  } else {
    echo "   ❌ Fichier introuvable: $fullPath\n";
    echo "   💡 Solution: Réuploadez l'image via l'admin\n";
  }

  // Vérifier AssetHelper
  echo "\n3️⃣  Test AssetHelper\n";
  echo "────────────────────────────────────────────────────\n";

  // Simuler AssetHelper::url()
  $url = $imagePath;
  if (strpos($url, '/') === 0) {
    $url = substr($url, 1);
  }
  if (strpos($url, 'uploads/') === 0) {
    $url = substr($url, 8);
  }
  $assetUrl = 'asset.php?f=' . urlencode($url);

  echo "   URL générée: $assetUrl\n";

  echo "\n4️⃣  Vérification asset.php\n";
  echo "────────────────────────────────────────────────────\n";

  $assetPhp = __DIR__ . '/public/asset.php';
  if (file_exists($assetPhp)) {
    echo "   ✅ asset.php existe\n";
  } else {
    echo "   ❌ asset.php n'existe pas!\n";
  }

  // Test direct
  echo "\n   Test de requête asset.php:\n";
  echo "   GET: $assetUrl\n";

  // Vérifier les chemins normalisés
  $uploadsPath = __DIR__ . '/public/uploads/' . $url;
  if (file_exists($uploadsPath)) {
    echo "   ✅ asset.php peut servir le fichier\n";
  } else {
    echo "   ❌ asset.php ne trouvera pas le fichier\n";
    echo "   Chemins attendus:\n";
    echo "      • assets/$url\n";
    echo "      • uploads/$url\n";
  }

} catch (\MongoDB\Driver\Exception\Exception $e) {
  echo "❌ Erreur MongoDB: " . $e->getMessage() . "\n";
  exit(1);
} catch (Exception $e) {
  echo "❌ Erreur: " . $e->getMessage() . "\n";
  exit(1);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "   Diagnostic Terminé\n";
echo "═══════════════════════════════════════════════════════════════\n";
