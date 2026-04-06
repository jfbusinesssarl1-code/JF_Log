<?php
require_once 'vendor/autoload.php';
$model = new App\Models\CompteModel();
$comptes = $model->getAll();
echo "Total comptes: " . count($comptes) . PHP_EOL;
if (count($comptes) > 0) {
    echo "Premiers comptes:" . PHP_EOL;
    for ($i = 0; $i < min(5, count($comptes)); $i++) {
        echo "  " . $comptes[$i]['code'] . " - " . $comptes[$i]['intitule'] . PHP_EOL;
    }
} else {
    echo "ERREUR: Aucun compte trouvé!" . PHP_EOL;
    echo "Vérification du fichier PLAN.xlsx..." . PHP_EOL;
    $planPath = realpath(__DIR__ . '/public/PLAN.xlsx');
    echo "Chemin: " . ($planPath ?: 'NON TROUVÉ') . PHP_EOL;
    echo "Existe: " . (is_file($planPath) ? 'OUI' : 'NON') . PHP_EOL;
    echo "Lisible: " . (is_readable($planPath) ? 'OUI' : 'NON') . PHP_EOL;
}
?>
