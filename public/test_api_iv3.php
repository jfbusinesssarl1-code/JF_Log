<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$image = $manager->create(100, 100);
$image->fill('red');

echo "=== Méthodes save/format ===\n";
$methods = get_class_methods($image);
foreach ($methods as $m) {
    if (stripos($m, 'save') !== false || stripos($m, 'format') !== false || stripos($m, 'encode') !== false) {
        echo "- $m\n";
    }
}

echo "\n=== Test save ---\n";
$testPath = __DIR__ . '/uploads/test_api.webp';
try {
    // Test 1: save simple
    $image->save($testPath);
    echo "✓ save() OK\n";
} catch (Error $e) {
    echo "✗ save(): " . $e->getMessage() . "\n";
}

// Test 2: Check what methods exist for encoding
echo "\n=== Test encode/toWebp ===\n";
if (method_exists($image, 'encode')) {
    echo "✓ encode() existe\n";
}
if (method_exists($image, 'toWebp')) {
    echo "✓ toWebp() existe\n";
}
if (method_exists($image, 'toPng')) {
    echo "✓ toPng() existe\n";
}

echo "\n=== Test save avec format ===\n";
try {
    $image->save($testPath, format: 'webp', quality: 80);
    echo "✓ save avec 'format' OK\n";
} catch (Error $e) {
    echo "✗ save avec 'format': " . $e->getMessage() . "\n";
}
