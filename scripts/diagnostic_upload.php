<?php
/**
 * Diagnostic - Vérifier pourquoi ImageConverterV2 ne sauve pas les images
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers/ImageConverterV2.php';

use App\Helpers\ImageConverterV2;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC: ImageConverterV2 - Pourquoi pas d'upload?\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// 1. Vérifier que le chemin est correct
$testImagePath = __DIR__ . '/../public/test_large_image.png';
echo "1️⃣  Vérification fichier source\n";
echo "   Chemin: $testImagePath\n";
echo "   Existe: " . (file_exists($testImagePath) ? "OUI" : "NON") . "\n";
if (file_exists($testImagePath)) {
    echo "   Taille: " . filesize($testImagePath) . " bytes\n";
}
echo "\n";

// 2. Vérifier les répertoires uploads
echo "2️⃣  Vérification répertoires uploads\n";
$uploadsDir = __DIR__ . '/../public/uploads';
$adminDir = $uploadsDir . '/admin';
$activitiesDir = $adminDir . '/activities';

echo "   Uploads dir: $uploadsDir\n";
echo "   Existe: " . (is_dir($uploadsDir) ? "OUI" : "NON") . "\n";
echo "   Writable: " . (is_writable($uploadsDir) ? "OUI" : "NON") . "\n";

echo "\n   Admin dir: $adminDir\n";
echo "   Existe: " . (is_dir($adminDir) ? "OUI" : "NON") . "\n";
echo "   Writable: " . (is_writable($adminDir) ? "OUI" : "NON") . "\n";

echo "\n   Activities dir: $activitiesDir\n";
echo "   Existe: " . (is_dir($activitiesDir) ? "OUI" : "NON") . "\n";
echo "   Writable: " . (is_writable($activitiesDir) ? "OUI" : "NON") . "\n";
echo "\n";

// 3. Tester l'upload directement
echo "3️⃣  Test conversion directe\n";
if (file_exists($testImagePath)) {
    $uploadedFile = [
        'name' => 'diag-test.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => $testImagePath,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($testImagePath)
    ];
    
    echo "   Appel convertUploadedFile...\n";
    $result = ImageConverterV2::convertUploadedFile($uploadedFile, 'admin/activities', 'webp');
    
    echo "   Résultat: " . ($result ? "SUCCÈS" : "ÉCHEC") . "\n";
    if ($result) {
        echo "   Chemin retourné: $result\n";
        
        $fullPath = __DIR__ . '/../public' . $result;
        echo "\n   Vérification fichier sauvegardé:\n";
        echo "   Chemin: $fullPath\n";
        echo "   Existe: " . (file_exists($fullPath) ? "OUI ✅" : "NON ❌") . "\n";
        
        if (file_exists($fullPath)) {
            echo "   Taille: " . filesize($fullPath) . " bytes\n";
            echo "   Readable: " . (is_readable($fullPath) ? "OUI" : "NON") . "\n";
        } else {
            echo "\n   ⚠️  PROBLÈME: Fichier censé être à $fullPath mais absent!\n";
            
            // Chercher le fichier
            echo "\n   Cherche fichiers récents dans activities:\n";
            $files = glob($activitiesDir . '/*', GLOB_MARK);
            if ($files) {
                foreach (array_slice($files, -5) as $f) {
                    echo "     - " . basename($f) . " (" . (is_dir($f) ? "DIR" : filesize($f) . " bytes") . ")\n";
                }
            } else {
                echo "     (aucun fichier)\n";
            }
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "Diagnostic terminé\n";
echo "═══════════════════════════════════════════════════════════════════\n";
