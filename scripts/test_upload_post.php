<?php
/**
 * Teste l'upload réel via POST (simule le formulaire)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers/ImageConverterV2.php';

use App\Helpers\ImageConverterV2;

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🧪 TEST UPLOAD VIA POST (Simule formulaire admin)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$testImagePath = __DIR__ . '/../public/test_large_image.png';

if (!file_exists($testImagePath)) {
    echo "❌ Image test non trouvée: $testImagePath\n";
    echo "Créer d'abord avec: php scripts/create_large_test_image.php\n";
    exit(1);
}

// Copier l'image dans un fichier temporaire pour simuler $_FILES
$tmpName = tempnam(sys_get_temp_dir(), 'upload_');
copy($testImagePath, $tmpName);

echo "1️⃣  Simulation de \$_FILES['activityImage']\n";
$_FILES = [
    'activityImage' => [
        'name' => 'test-activity-' . date('Y-m-d-H-i-s') . '.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => $tmpName,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($testImagePath)
    ]
];

echo "   Name: " . $_FILES['activityImage']['name'] . "\n";
echo "   Size: " . $_FILES['activityImage']['size'] . " bytes\n";
echo "   Tmp: " . $_FILES['activityImage']['tmp_name'] . "\n\n";

// 2. Appel ImageConverterV2
echo "2️⃣  Appel ImageConverterV2::convertUploadedFile()\n";
$result = ImageConverterV2::convertUploadedFile($_FILES['activityImage'], 'admin/activities', 'webp');

echo "   Résultat: " . ($result ? "✅ SUCCÈS" : "❌ ÉCHEC") . "\n";

if ($result) {
    echo "   Chemin retourné: $result\n";
    
    $fullPath = __DIR__ . $result;
    $fileExists = file_exists($fullPath);
    $fileSize = $fileExists ? filesize($fullPath) : 0;
    
    echo "\n3️⃣  Vérification fichier\n";
    echo "   Chemin complet: $fullPath\n";
    echo "   Existe: " . ($fileExists ? "✅ OUI" : "❌ NON") . "\n";
    if ($fileExists) {
        echo "   Taille: $fileSize bytes\n";
        echo "   Readable: " . (is_readable($fullPath) ? "OUI" : "NON") . "\n";
    }
} else {
    echo "   Voir les logs pour plus de détails\n";
}

// Cleanup
@unlink($tmpName);

echo "\n4️⃣  Vérification fichiers créés\n";
$activitiesDir = __DIR__ . '/uploads/admin/activities';
$webpFiles = glob($activitiesDir . '/*.webp');
echo "   WebP files in activities: " . count($webpFiles) . "\n";
if ($webpFiles) {
    foreach (array_slice($webpFiles, -3) as $f) {
        echo "     - " . basename($f) . " (" . filesize($f) . " bytes)\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "✅ Test terminé\n";
echo "Voir les logs avec: php public/view_logs.php\n";
echo "═══════════════════════════════════════════════════════════════════\n";
