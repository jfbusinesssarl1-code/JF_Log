<?php
/**
 * Affiche les logs ImageConverterV2 pour debug
 */

$logFile = __DIR__ . '/uploads/imageconverter.log';

echo "═══════════════════════════════════════════════════════════════════\n";
echo "📋 LOGS ImageConverterV2\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

if (!file_exists($logFile)) {
    echo "⚠️  Fichier log non encore créé: $logFile\n";
    echo "Les uploads vont créer ce fichier automatiquement.\n";
} else {
    echo "Fichier log: $logFile\n";
    echo "Taille: " . filesize($logFile) . " bytes\n";
    echo "Dernière modification: " . date('Y-m-d H:i:s', filemtime($logFile)) . "\n\n";
    
    $lines = file($logFile);
    $totalLines = count($lines);
    
    echo "Affichage des 50 dernières lignes ($totalLines total):\n";
    echo str_repeat("─", 70) . "\n";
    
    foreach (array_slice($lines, -50) as $line) {
        echo $line;
    }
    
    echo str_repeat("─", 70) . "\n\n";
    
    if ($totalLines > 50) {
        echo "💡 Affiche les 50 dernières lignes sur $totalLines au total\n";
    }
}

echo "\n<a href='test_upload_form.php'>← Retour au formulaire de test</a>\n";
