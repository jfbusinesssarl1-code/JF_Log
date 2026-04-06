<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

$client = new Client('mongodb://localhost:27017');
$db = $client->selectDatabase('cb_jf');
$activities = $db->selectCollection('activities');

$count = $activities->countDocuments();

echo "═══════════════════════════════════════════════════════════════\n";
echo "   📋 Toutes les Activités Existantes\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Total activités dans la BD: $count\n\n";

if ($count === 0) {
  echo "❌ AUCUNE ACTIVITÉ N'EXISTE!\n\n";
  echo "💡 Solution: Créer une activité 'Pont Maghulinga' via Admin Panel\n";
  echo "   Admin > Activités > Ajouter Activité\n";
} else {
  echo "Activités trouvées:\n";
  echo "────────────────────────────────────────────────────\n";

  $i = 1;
  foreach ($activities->find() as $activity) {
    echo "\n$i. Titre: " . htmlspecialchars($activity['title'] ?? 'Sans titre') . "\n";
    echo "   Status: " . ($activity['status'] ?? 'N/A') . "\n";
    echo "   Date: " . ($activity['date'] ?? 'N/A') . "\n";

    if (!empty($activity['image'])) {
      echo "   Image: ✅ " . $activity['image'] . "\n";
    } else {
      echo "   Image: ❌ Aucune\n";
    }

    $i++;
  }

  echo "\n\n════════════════════════════════════════════════════════════════\n";

  // Vérifier si Maghulinga existe
  $maghulinga = $activities->findOne(['title' => new \MongoDB\BSON\Regex('maghulinga', 'i')]);

  if ($maghulinga === null) {
    echo "⚠️  'Pont Maghulinga' N'EXISTE PAS!\n";
    echo "\n💡 Action requise:\n";
    echo "   1. Aller à: Admin Panel > Activités\n";
    echo "   2. Cliquer: Ajouter Activité\n";
    echo "   3. Remplir:\n";
    echo "      • Titre: Pont Maghulinga\n";
    echo "      • Description: [Votre description]\n";
    echo "      • Status: En cours (ou votre choix)\n";
    echo "      • Date: 2026-02-14\n";
    echo "      • Image: [Choisir une photo du pont]\n";
    echo "   4. Cliquer: Enregistrer\n";
    echo "   5. ✅ Image s'affichera automatiquement (convertie en WebP)!\n";
  } else {
    echo "✅ 'Pont Maghulinga' EXISTE!\n";
    echo "   Titre: " . $maghulinga['title'] . "\n";

    if (empty($maghulinga['image'])) {
      echo "   ❌ MAIS: Aucune image n'est associée!\n";
      echo "\n   Solution:\n";
      echo "   1. Admin > Activités > Modifier 'Pont Maghulinga'\n";
      echo "   2. Uploader une image\n";
      echo "   3. Enregistrer\n";
    } else {
      echo "   Image: " . $maghulinga['image'] . "\n";

      // Vérifier si le fichier existe
      $imagePath = str_replace('/uploads/', '', $maghulinga['image']);
      $fullPath = __DIR__ . '/../public/uploads/' . $imagePath;

      if (file_exists($fullPath)) {
        echo "   Fichier: ✅ Existe\n";
      } else {
        echo "   Fichier: ❌ Introuvable au chemin: $fullPath\n";
        echo "\n   Solution:\n";
        echo "   1. Réuploader l'image via Admin > Activités\n";
        echo "   2. Vérifier les permissions: chmod 755 public/uploads/admin/activities/\n";
      }
    }
  }
}

echo "\n════════════════════════════════════════════════════════════════\n";
?>