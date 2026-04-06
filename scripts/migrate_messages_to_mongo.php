<?php
// Simple migration script: move messages from app/data/messages.json to MongoDB
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../init.php';

use App\Models\MessageModel;

$dataFile = __DIR__ . '/../app/data/messages.json';
if (!file_exists($dataFile)) {
  echo "No data file found at $dataFile\n";
  exit(0);
}

$json = file_get_contents($dataFile);
$items = json_decode($json, true);
if (empty($items) || !is_array($items)) {
  echo "No messages to migrate.\n";
  exit(0);
}

$msgModel = new MessageModel();
$count = 0;
foreach ($items as $it) {
  $it = (array) $it;
  // normalize
  $doc = [
    'name' => $it['name'] ?? ($it['nom'] ?? ''),
    'email' => $it['email'] ?? '',
    'subject' => $it['subject'] ?? '',
    'message' => $it['message'] ?? ($it['contenu'] ?? ''),
    'ip' => $it['ip'] ?? null
  ];
  $msgModel->insert($doc);
  $count++;
}
// backup
copy($dataFile, $dataFile . '.bak.' . time());
// optionally delete the original file
// unlink($dataFile);

echo "Migrated $count messages to MongoDB. Backup created.\n";

?>