<?php
/**
 * Script de diagnostic complet pour Maghulinga
 * Utilise la config réelle du projet
 */

require 'vendor/autoload.php';

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC COMPLET: Pont Maghulinga\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    // Utiliser la même config que le projet
    $uri = $_SERVER['MONGODB_URI'] ?? getenv('MONGODB_URI') ?: 'mongodb://127.0.0.1:27017';
    $dbName = 'compta';
    
    echo "📡 Connexion MongoDB...\n";
    echo "   URI: " . (strpos($uri, 'password') !== false ? 'mongodb+srv://[MASKED]' : $uri) . "\n";
    echo "   DB: $dbName\n\n";
    
    $client = new MongoDB\Client($uri, ['serverSelectionTimeoutMS' => 5000]);
    
    // Test de connexion
    $client->listDatabases();
    echo "✅ Connecté!\n\n";

    $db = $client->selectDatabase($dbName);
    $activities = $db->selectCollection('activities');

    echo "═══════════════════════════════════════════════════════════════\n";
    echo "📋 RECHERCHE: Activités contenant 'maghulinga'\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    $cursor = $activities->find(
        ['title' => new MongoDB\BSON\Regex('maghulinga', 'i')]
    );

    $found = false;
    foreach ($cursor as $activity) {
        $found = true;
        echo "✅ TROUVÉE!\n";
        echo "───────────────────────────────────────────────────────────\n";
        echo "Titre: " . $activity['title'] . "\n";
        echo "Date: " . ($activity['date'] ?? 'N/A') . "\n";
        echo "Description: " . (substr($activity['description'] ?? '', 0, 50)) . "...\n";
        echo "Image en BD: " . ($activity['image'] ?? '❌ AUCUNE') . "\n";
        echo "ID: " . $activity['_id'] . "\n\n";

        if (!empty($activity['image'])) {
            $imagePath = $activity['image'];
            $fileName = basename($imagePath);
            $fullPath = __DIR__ . '/public' . $imagePath;
            
            echo "🔍 Vérification du fichier:\n";
            echo "   Chemin en BD: $imagePath\n";
            echo "   Nom fichier: $fileName\n";
            echo "   Chemin complet: $fullPath\n";
            echo "   Existe? " . (file_exists($fullPath) ? '✅ OUI' : '❌ NON') . "\n\n";

            if (!file_exists($fullPath)) {
                echo "💡 SOLUTION:\n";
                echo "   L'image n'existe pas au chemin enregistré.\n";
                echo "   Options:\n";
                echo "   1. Supprimer le champ 'image' de l'enregistrement\n";
                echo "   2. Re-uploader l'image via Admin → Activités\n";
                echo "   3. Chercher le fichier ailleurs\n\n";
                
                // Chercher partout
                $searchName = '*' . $fileName . '*';
                $found_files = [];
                exec("dir /s /b \"C:\\\\Users\\\\*\\\\*$searchName\" 2>nul", $found_files);
                if (!empty($found_files)) {
                    echo "   🔎 Fichier trouvé ailleurs:\n";
                    foreach ($found_files as $f) {
                        if (strlen($f) > 0) echo "      → $f\n";
                    }
                }
            }
        } else {
            echo "⚠️  Le champ image est vide en BD\n\n";
        }
    }

    if (!$found) {
        echo "❌ Aucune activité 'Maghulinga' trouvée\n";
        echo "\n   Activités existantes:\n";
        $all = $activities->find();
        foreach ($all as $a) {
            echo "   • " . $a['title'] . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "\n💡 Vérifiez:\n";
    echo "   • MongoDB Atlas accessible\n";
    echo "   • Identifiants corrects\n";
    echo "   • Connexion internet stable\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
