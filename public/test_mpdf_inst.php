<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: text/plain; charset=utf-8');
try {
  $m = new \Mpdf\Mpdf(['mode' => 'utf-8']);
  echo "mpdf instantiation OK\n";
  echo "version: " . \Mpdf\Mpdf::VERSION . "\n";
} catch (Throwable $e) {
  echo "mpdf instantiation FAILED: " . $e->getMessage() . "\n";
}
