<?php
/**
 * Script pour corriger l'activité Maghulinga
 * Supprime le champ image invalide
 */

require 'vendor/autoload.php';

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🔧 CORRECTION: Activité Pont Maghulinga\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $uri = $_SERVER['MONGODB_URI'] ?? getenv('MONGODB_URI') ?: 'mongodb://127.0.0.1:27017';
    $client = new MongoDB\Client($uri, ['serverSelectionTimeoutMS' => 5000]);
    $db = $client->selectDatabase('compta');
    $activities = $db->selectCollection('activities');

    // Chercher l'activité Maghulinga
    $activity = $activities->findOne(
        ['title' => new MongoDB\BSON\Regex('maghulinga', 'i')]
    );

    if (!$activity) {
        echo "❌ Activité non trouvée\n";
        exit(1);
    }

    echo "Activité trouvée: " . $activity['title'] . "\n";
    echo "Image actuelle: " . ($activity['image'] ?? '(aucune)') . "\n\n";

    echo "→ Suppression du champ image invalide...\n";
    
    $result = $activities->updateOne(
        ['_id' => $activity['_id']],
        ['$unset' => ['image' => 1]]
    );

    if ($result->getModifiedCount() > 0) {
        echo "\n✅ CORRIGÉ!\n\n";
        echo "✓ Le champ image a été supprimé de la BD\n";
        echo "✓ L'activité 'Construction pont Maghulinga' s'affiche maintenant SANS image\n";
        echo "✓ Vous pouvez maintenant re-uploader l'image via:\n";
        echo "  Admin → Activités → Modifier → Uploader l'image\n\n";
    } else {
        echo "\n⚠️ Aucune modification appliquée\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
