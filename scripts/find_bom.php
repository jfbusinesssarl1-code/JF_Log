<?php
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../'));
$matches = [];
foreach ($it as $f) {
  if ($f->isFile() && substr($f->getFilename(), -4) === '.php') {
    $name = $f->getPathname();
    $h = fopen($name, 'rb');
    $b = fread($h, 3);
    fclose($h);
    if ($b === "\xEF\xBB\xBF") {
      $matches[] = $name;
    }
  }
}
if (empty($matches)) {
  echo "No BOM found\n";
} else {
  echo "Files with BOM:\n";
  foreach ($matches as $m)
    echo $m . "\n";
}
