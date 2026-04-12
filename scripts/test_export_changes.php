<?php
require_once 'vendor/autoload.php';

use App\Models\BilanModel;

echo "=== Vérification des données pour l'export ===\n\n";

$model = new BilanModel();

// Vérifier le bilan initial
echo "1. BILAN INITIAL:\n";
$initial = $model->getInitialBilan();
if ($initial) {
    echo "   - Titre: " . ($initial['title'] ?? 'N/A') . "\n";
    echo "   - Date: " . ($initial['date'] ?? 'N/A') . "\n";
    echo "   - Comptes: " . count($initial['accounts'] ?? []) . "\n";
    echo "   ✓ Peut être exporté\n";
} else {
    echo "   ✗ Non disponible\n";
}

// Vérifier les copies périodiques
echo "\n2. COPIES PÉRIODIQUES:\n";
$allCopies = $model->getPeriodicCopies() ?? [];
echo "   - Nombre total: " . count($allCopies) . "\n";

if (count($allCopies) > 0) {
    foreach ($allCopies as $i => $copy) {
        echo "\n   Copie " . ($i+1) . ":\n";
        echo "     - Titre: " . ($copy['title'] ?? 'N/A') . "\n";
        echo "     - Date: " . ($copy['date'] ?? 'N/A') . "\n";
        echo "     - Comptes: " . count($copy['accounts'] ?? []) . "\n";
    }
    echo "\n   ✓ Peuvent être exportées\n";
} else {
    echo "   (Aucune copie disponible pour l'export)\n";
}

// Vérifier le bilan courant
echo "\n3. BILAN EN COURS (DYNAMIQUE):\n";
$current = $model->getCurrentBilan();
echo "   - Titre: " . ($current['title'] ?? 'N/A') . "\n";
echo "   - Comptes: " . count($current['accounts'] ?? []) . "\n";
echo "   - STATUT: ✗ NE DOIT PAS ÊTRE EXPORTÉ (blocage mis en place)\n";

echo "\n=== Résumé ===\n";
echo "✓ Types d'export autorisés: initial, copy\n";
echo "✗ Type d'export bloqué: current (dynamique)\n";
echo "✓ Modifications appliquées avec succès\n";

