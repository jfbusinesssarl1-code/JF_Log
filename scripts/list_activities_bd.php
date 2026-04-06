<?php
/**
 * Script simple pour lister les activités
 */

require 'vendor/autoload.php';

// Connexion MongoDB
$client = new MongoDB\Client('mongodb://localhost:27017', ['serverSelectionTimeoutMS' => 5000]);
$db = $client->selectDatabase('cb_jf');
$activities = $db->selectCollection('activities');

// Lister toutes les activités
$cursor = $activities->find();
$count = 0;

echo "════════════════════════════════════════════════════════\n";
echo "📋 TOUTES LES ACTIVITÉS EN BD\n";
echo "════════════════════════════════════════════════════════\n\n";

foreach ($cursor as $activity) {
    $count++;
    echo "$count. Titre: " . ($activity['title'] ?? 'N/A') . "\n";
    echo "   Image: " . ($activity['image'] ?? '❌ AUCUNE') . "\n";
    echo "   ID: " . $activity['_id'] . "\n";
    echo "\n";
}

echo "════════════════════════════════════════════════════════\n";
echo "Total: $count activités\n";
