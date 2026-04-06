<?php
/**
 * Crée une image test pour tester les uploads
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

echo "Création d'une image test...\n";

$manager = new ImageManager(new Driver());

// Créer une image 2000x1500 avec du contenu varié
$image = $manager->create(2000, 1500);

// Remplir avec une couleur
$image->fill('red');

// Sauvegarder en PNG (qui reste assez volumineux même après compression)
$testPath = __DIR__ . '/../public/test_large_image.png';

// Sauvegarder avec Intervention (qualité 90)
$image->save($testPath, quality: 90);

$size = filesize($testPath);
echo "✅ Image créée: " . number_format($size / 1024, 2) . " KB\n";
echo "   Chemin: $testPath\n";
echo "\nCette image sera utilisée pour tester:\n";
echo "1. Téléversement de fichier de grande taille\n";
echo "2. Compression par ImageConverterV2\n";
echo "3. Sauvegarde en WebP optimisé\n";
