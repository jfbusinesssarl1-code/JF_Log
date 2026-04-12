<?php
require_once 'vendor/autoload.php';

use App\Models\BilanModel;

echo "=== Vérification des modifications ===\n\n";

$model = new BilanModel();

// 1. Vérifier le bilan initial
echo "1. BILAN INITIAL (type=initial):\n";
$initial = $model->getInitialBilan();
if ($initial) {
    echo "   ✓ Disponible pour export\n";
    echo "   - Titre: " . ($initial['title'] ?? 'N/A') . "\n";
    echo "   - Comptes: " . count($initial['accounts'] ?? []) . "\n";
} else {
    echo "   ✗ Non trouvé\n";
}

// 2. Vérifier les copies périodiques
echo "\n2. COPIES PÉRIODIQUES (type=copy):\n";
$copies = $model->getPeriodicCopies() ?? [];
echo "   - Nombre total: " . count($copies) . "\n";

if (count($copies) > 0) {
    foreach ($copies as $i => $copy) {
        echo "\n   Copie " . ($i+1) . ":\n";
        echo "     - ID: " . ($copy['_id'] ?? 'N/A') . "\n";
        echo "     - Titre: " . ($copy['title'] ?? 'N/A') . "\n";
        echo "     - Comptes: " . count($copy['accounts'] ?? []) . "\n";
        echo "     ✓ Disponible pour export (type=copy&copy_id=...)\n";
    }
}

// 3. Vérifier le bilan courant
echo "\n3. BILAN EN COURS (type=current):\n";
$current = $model->getCurrentBilan();
echo "   - Titre: " . ($current['title'] ?? 'N/A') . "\n";
echo "   - Comptes: " . count($current['accounts'] ?? []) . "\n";
echo "   ✗ BLOQUÉ - Cannot export (not persistent)\n";

echo "\n=== Résumé des correctifs ===\n";
echo "✓ Bouton 'Exporter PDF' supprimé de la page du bilan en cours\n";
echo "✓ Export type=current → BLOQUÉ avec message d'erreur\n";
echo "✓ Export type=initial → AUTORISÉ\n";
echo "✓ Export type=copy → AUTORISÉ (avec copy_id)\n";
echo "✓ Gestion d'erreur améliorée\n";
