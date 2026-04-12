<?php
// Script de diagnostic pour vérifier les données du bilan
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\BilanModel;

echo "=== DIAGNOSTIC BILAN ===\n\n";

$model = new BilanModel();

// Vérifier le bilan initial
echo "1. BILAN INITIAL:\n";
$initial = $model->getInitialBilan();
if ($initial) {
    echo "   Titre: " . ($initial['title'] ?? 'N/A') . "\n";
    echo "   Date: " . ($initial['date'] ?? 'N/A') . "\n";
    echo "   Created_at: " . (isset($initial['created_at']) ? $initial['created_at']->toDateTime()->format('Y-m-d H:i:s') : 'N/A') . "\n";
    echo "   Updated_at: " . (isset($initial['updated_at']) ? $initial['updated_at']->toDateTime()->format('Y-m-d H:i:s') : 'N/A') . "\n";
} else {
    echo "   Aucun bilan initial trouvé\n";
}

echo "\n2. DERNIERE COPIE PERIODIQUE:\n";
$latestCopy = $model->getLatestPeriodicCopy();
if ($latestCopy) {
    echo "   Titre: " . ($latestCopy['title'] ?? 'N/A') . "\n";
    echo "   Date: " . ($latestCopy['date'] ?? 'N/A') . "\n";
    echo "   Status: " . ($latestCopy['status'] ?? 'N/A') . "\n";
    echo "   Created_at: " . (isset($latestCopy['created_at']) ? $latestCopy['created_at']->toDateTime()->format('Y-m-d H:i:s') : 'N/A') . "\n";
} else {
    echo "   Aucune copie périodique trouvée\n";
}

echo "\n3. BILAN COURANT (GÉNÉRÉ):\n";
$current = $model->getCurrentBilan();
echo "   Titre: " . ($current['title'] ?? 'N/A') . "\n";
echo "   Date: " . ($current['date'] ?? 'N/A') . "\n";

echo "\n4. TOUTES LES COPIES PERIODIQUES:\n";
$copies = $model->getPeriodicCopies();
if (empty($copies)) {
    echo "   Aucune copie trouvée\n";
} else {
    foreach ($copies as $i => $copy) {
        echo "   Copie " . ($i + 1) . ":\n";
        echo "     ID: " . ($copy['_id'] ?? 'N/A') . "\n";
        echo "     Titre: " . ($copy['title'] ?? 'N/A') . "\n";
        echo "     Date: " . ($copy['date'] ?? 'N/A') . "\n";
        echo "     Status: " . ($copy['status'] ?? 'N/A') . "\n";
        echo "     Created_at: " . (isset($copy['created_at']) ? $copy['created_at']->toDateTime()->format('Y-m-d H:i:s') : 'N/A') . "\n";
        echo "\n";
    }
}

echo "=== FIN DIAGNOSTIC ===\n";
?>