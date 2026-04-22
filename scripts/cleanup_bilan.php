<?php
/**
 * Script de nettoyage de la collection bilan_initial
 * Supprime les documents invalides avec _id: null ou sans type
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\BilanModel;

try {
    echo "Nettoyage de la collection bilan_initial...\n";
    
    $bilanModel = new BilanModel();
    $result = $bilanModel->cleanupInvalidDocuments();
    
    if ($result) {
        echo "✓ Nettoyage réussi!\n";
        echo "Les documents invalides ont été supprimés.\n";
        echo "Le bilan initial sera maintenant géré correctement.\n";
    } else {
        echo "✗ Erreur lors du nettoyage.\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
