<?php
echo "Starting test script...\n";
require_once 'vendor/autoload.php';
echo "Autoload loaded\n";

use App\Models\BilanModel;

echo "Creating BilanModel...\n";
$model = new BilanModel();

echo "=== Testing BilanModel data retrieval ===\n";

echo "\n1. getCurrentBilan():\n";
try {
    $current = $model->getCurrentBilan();
    echo "Title: " . ($current['title'] ?? 'N/A') . "\n";
    echo "Date: " . ($current['date'] ?? 'N/A') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n2. getInitialBilan():\n";
$initial = $model->getInitialBilan();
if ($initial) {
    echo "Title: " . ($initial['title'] ?? 'N/A') . "\n";
    echo "Date: " . ($initial['date'] ?? 'N/A') . "\n";
    if (isset($initial['created_at']) && $initial['created_at'] instanceof \MongoDB\BSON\UTCDateTime) {
        echo "Created At: " . $initial['created_at']->toDateTime()->format('d/m/Y H:i') . "\n";
    }
} else {
    echo "No initial bilan found\n";
}

echo "\n3. getLatestPeriodicCopy():\n";
$latest = $model->getLatestPeriodicCopy();
if ($latest) {
    echo "Title: " . ($latest['title'] ?? 'N/A') . "\n";
    echo "Date: " . ($latest['date'] ?? 'N/A') . "\n";
    if (isset($latest['created_at']) && $latest['created_at'] instanceof \MongoDB\BSON\UTCDateTime) {
        echo "Created At: " . $latest['created_at']->toDateTime()->format('d/m/Y H:i') . "\n";
    }
} else {
    echo "No periodic copies found\n";
}

echo "\n=== Test completed ===\n";