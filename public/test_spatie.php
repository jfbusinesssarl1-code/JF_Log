<?php
require 'vendor/autoload.php';

$classes = get_declared_classes();
$spatie = array_filter($classes, fn($c) => strpos($c, 'Spatie') !== false);

echo "Classes Spatie trouvées: " . count($spatie) . PHP_EOL;
foreach(array_slice($spatie, 0, 10) as $c) {
    echo "- $c\n";
}

echo "\nTest direct:\n";
if (class_exists('Spatie\ImageOptimizer\ImageOptimizer')) {
    echo "✓ ImageOptimizer trouvé!\n";
} else {
    echo "✗ ImageOptimizer NON trouvé\n";
}

// Vérifier if Spatie require un tool externe
echo "\n=== Vérifications ===\n";
if (class_exists('Spatie\ImageOptimizer\ImageOptimizer', false)) {
    echo "ImageOptimizer est directement disponible\n";
} else {
    // Essayer d'autoloader
    require_once 'vendor/spatie/image-optimizer/src/ImageOptimizer.php';
    if (class_exists('Spatie\ImageOptimizer\ImageOptimizer', false)) {
        echo "ImageOptimizer trouvé après require manuel\n";
    } else {
        echo "ImageOptimizer pas trouvable même après require\n";
    }
}
