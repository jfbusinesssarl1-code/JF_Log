<?php
/**
 * Script de diagnostic - Vérifier l'activité Maghulinga en BD
 */

require '../vendor/autoload.php';

$client = new MongoDB\Client('mongodb://localhost:27017');
$db = $client->selectDatabase('cb_jf');
$activities = $db->selectCollection('activities');

echo "═══════════════════════════════════════════════════════════════\n";
echo "🌉 Diagnostic: Activité Pont Maghulinga\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Chercher l'activité
$activity = $activities->findOne(['title' => new MongoDB\BSON\Regex('maghulinga', 'i')]);

if (!$activity) {
    echo "❌ Aucune activité trouvée\n";
    exit(1);
}

echo "✅ Activité trouvée en BD\n";
echo "────────────────────────────────────────────────────\n";
echo "📋 Titre: " . $activity['title'] . "\n";
echo "📅 Date: " . ($activity['date'] ?? 'N/A') . "\n";
echo "📝 Description: " . (strlen($activity['description'] ?? '') > 50 ? substr($activity['description'], 0, 50) . '...' : ($activity['description'] ?? 'N/A')) . "\n";
echo "🖼️  Image en BD: " . ($activity['image'] ?? '❌ AUCUNE') . "\n";
echo "🆔 ID MongoDB: " . $activity['_id'] . "\n";

if (empty($activity['image'])) {
    echo "\n❌ PROBLÈME: L'image n'est pas stockée en BD\n";
    exit(1);
}

// Vérifier le chemin
$imagePath = $activity['image'];
$fullPath = __DIR__ . '/../public' . $imagePath;
$fileName = basename($imagePath);
$uploadDir = __DIR__ . '/../public/uploads';

echo "\n\n═══════════════════════════════════════════════════════════════\n";
echo "📁 Vérification du Fichier\n";
echo "═══════════════════════════════════════════════════════════════\n";

echo "Chemin en BD: " . $imagePath . "\n";
echo "Chemin complet attendu: " . $fullPath . "\n";
echo "Existe? " . (file_exists($fullPath) ? '✅ OUI' : '❌ NON') . "\n";

if (file_exists($fullPath)) {
    echo "✅ Le fichier existe au bon endroit!\n";
    echo "   Taille: " . number_format(filesize($fullPath) / 1024, 2) . " KB\n";
    $info = @getimagesize($fullPath);
    if ($info) {
        echo "   Dimensions: " . $info[0] . " × " . $info[1] . " px\n";
        echo "   Type: " . $info['mime'] . "\n";
    }
} else {
    echo "\n❌ PROBLÈME: Le fichier n'existe pas au chemin attendu!\n";
    echo "   Cherche le fichier '$fileName' dans uploads...\n\n";
    
    // Chercher le fichier
    $found = [];
    $directoriesToScan = [
        $uploadDir . '/admin/activities',
        $uploadDir . '/admin/home',
        $uploadDir . '/admin/about',
        $uploadDir . '/admin/services',
        $uploadDir . '/admin/partners'
    ];
    
    foreach ($directoriesToScan as $dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && stripos($file, $fileName) !== false) {
                    $found[] = $dir . '/' . $file;
                }
            }
        }
    }
    
    if (!empty($found)) {
        echo "🔍 Fichier trouvé à d'autres endroits:\n";
        foreach ($found as $path) {
            echo "   ✅ " . str_replace(__DIR__ . '/../', '', $path) . "\n";
        }
        
        echo "\n💡 SOLUTION: Le chemin en BD est incorrect!\n";
        echo "   Il faut mettre à jour la BD avec le chemin correct.\n";
        
        // Proposer de corriger
        if (count($found) === 1) {
            $correctPath = str_replace(__DIR__ . '/../public', '', $found[0]);
            echo "\n   ➡️  Chemin correct: $correctPath\n";
            
            // Corriger automatiquement
            $activities->updateOne(
                ['_id' => $activity['_id']],
                ['$set' => ['image' => $correctPath]]
            );
            
            echo "\n✅ BD MISE À JOUR avec le chemin correct!\n";
            echo "   L'image s'affichera maintenant correctement.\n";
        }
    } else {
        echo "❌ Fichier introuvable partout!\n";
        echo "\n💡 SOLUTIONS:\n";
        echo "   1. Vérifier que l'image a été uploadée\n";
        echo "   2. Recréer l'activité avec upload d'image\n";
        echo "   3. Vérifier les logs de upload\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";