<?php
require_once 'init.php';

use App\Controllers\BilanController;

// Simuler une requête GET pour type=current
$_GET['type'] = 'current';
$_GET['format'] = 'pdf';

echo "=== Testing export with type=current ===\n";
try {
    $controller = new BilanController();
    $controller->export();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Testing export with type=initial ===\n";
// Simuler une requête GET pour type=initial
$_GET['type'] = 'initial';
$_GET['format'] = 'pdf';

try {
    $controller = new BilanController();
    $controller->export();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test completed ===\n";