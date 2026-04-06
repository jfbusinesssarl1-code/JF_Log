<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$image = $manager->create(100, 100);
$image->fill('red');

echo "=== Testing Intervention Image 3.x API ===\n";

// Test 1: Direct save with format and quality
try {
    $image->save(__DIR__ . '/uploads/test1.webp', format: 'webp', quality: 80);
    echo "✓ save(path, format: 'webp', quality: 80) OK\n";
} catch (Error $e) {
    echo "✗ save() test: " . $e->getMessage() . "\n";
}

// Test 2: Save as PNG
try {
    $image->save(__DIR__ . '/uploads/test2.png', format: 'png');
    echo "✓ save(path, format: 'png') OK\n";
} catch (Error $e) {
    echo "✗ save() png: " . $e->getMessage() . "\n";
}

// Test 3: Save with default format (inferred from extension)
try {
    $image->save(__DIR__ . '/uploads/test3.jpg', quality: 75);
    echo "✓ save(path, quality: 75) OK\n";
} catch (Error $e) {
    echo "✗ save() inference: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
