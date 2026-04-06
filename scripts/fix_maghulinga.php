<?php
/**
 * Script pour corriger l'activité Maghulinga
 * Trois options:
 * 1. Supprimer l'activité complètement
 * 2. Vider le champ image (garder l'activité sans image)
 * 3. Corriger le chemin d'image s'il est mal enregistré
 */

require 'vendor/autoload.php';

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🔧 CORRECTION: Activité Ponte Maghulinga\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $client = new MongoDB\Client('mongodb://localhost:27017');
    $db = $client->selectDatabase('cb_jf');
    $activities = $db->selectCollection('activities');

    // Chercher l'activité Maghulinga
    $activity = $activities->findOne(['title' => new MongoDB\BSON\Regex('maghulinga', 'i')]);

    if (!$activity) {
        echo "❌ Activité Maghulinga non trouvée en BD\n";
        exit(1);
    }

    echo "✅ Activité trouvée: " . $activity['title'] . "\n\n";
    echo "Image en BD: " . ($activity['image'] ?? '(aucune)') . "\n\n";

    echo "CHOISISSEZ UNE OPTION:\n";
    echo "  1. SUPPRIMER l'activité complètement\n";
    echo "  2. VIDER le champ image (garder l'activité sans image)\n";
    echo "  3. GARDER COMME EST\n\n";

    // Pour ce script, on va automatiquement vider le champ image (option 2)
    // car c'est la solution sûre qui ne perd pas d'infos
    
    echo "→ Exécution: VIDAGE du champ image (Option 2)...\n";
    
    $result = $activities->updateOne(
        ['_id' => $activity['_id']],
        ['$unset' => ['image' => 1]]  // Supprime le field image
    );

    if ($result->getModifiedCount() > 0) {
        echo "\n✅ CORRIGÉ! Le champ image a été supprimé de la BD\n";
        echo "   L'activité 'Pont Maghulinga' existe maintenant SANS image\n";
        echo "   Vous pouvez re-uploader l'image via Admin → Activités\n";
    } else {
        echo "\n⚠️  Aucune modification (peut-être déjà corrigé?)\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
