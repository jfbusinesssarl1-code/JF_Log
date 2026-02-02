<?php
$files = [
  __DIR__ . '/../app/views/navbar.php',
  __DIR__ . '/../public/index.php'
];
foreach ($files as $file) {
  if (!file_exists($file)) {
    echo "File not found: $file\n";
    continue;
  }
  $content = file_get_contents($file);
  if (strpos($content, "\xEF\xBB\xBF") === 0) {
    $new = substr($content, 3);
    file_put_contents($file, $new);
    echo "Stripped BOM: $file\n";
  } else {
    echo "No BOM: $file\n";
  }
}
