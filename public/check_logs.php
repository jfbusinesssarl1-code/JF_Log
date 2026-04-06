<?php
/**
 * Affiche les derniers logs PHP pour debugger les uploads
 */

$logFile = ini_get('error_log');

if (!$logFile || !file_exists($logFile)) {
    echo "❌ Impossible de trouver le fichier log PHP\n";
    echo "error_log: " . ($logFile ? $logFile : 'non configuré') . "\n";
    exit(1);
}

echo "═══════════════════════════════════════════════════════════════════\n";
echo "📋 LOGS PHP - Upload Activités\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "File: $logFile\n\n";

// Lire les 50 dernières lignes du fichier log
$lines = array_slice(file($logFile), -50);

$adminLogs = [];
foreach ($lines as $line) {
    if (strpos($line, 'AdminController') !== false || 
        strpos($line, 'ImageConverterV2') !== false) {
        $adminLogs[] = trim($line);
    }
}

if (empty($adminLogs)) {
    echo "⚠️  Aucun log AdminController/ImageConverterV2 trouvé\n";
    echo "\nDernières 20 lignes du log:\n";
    foreach (array_slice($lines, -20) as $line) {
        echo $line;
    }
} else {
    echo count($adminLogs) . " lignes de log pertinentes trouvées:\n\n";
    foreach ($adminLogs as $log) {
        echo "$ $log\n";
    }
}
