<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: text/plain; charset=utf-8');
echo 'class_exists Mpdf? ' . (class_exists('\\Mpdf\\Mpdf') ? 'yes' : 'no') . "\n";
// Show phpinfo for debug
//phpinfo();
