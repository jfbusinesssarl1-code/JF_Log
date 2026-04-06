<?php
require 'vendor/autoload.php';

try {
    $client = new MongoDB\Client('mongodb://localhost:27017', ['serverSelectionTimeoutMS' => 3000]);
    $db = $client->selectDatabase('cb_jf');
    $activities = $db->selectCollection('activities');

    echo "Activités en BD:\n";
    $cursor = $activities->find();
    foreach ($cursor as $a) {
        echo "Titre: " . ($a['title'] ?? 'N/A') . " | Image: " . ($a['image'] ?? 'NONE') . "\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
