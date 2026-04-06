<?php
/**
 * Script CLI pour convertir les images en masse
 * Usage: php scripts/convert_images.php [directory] [format]
 * 
 * Exemples:
 *   php scripts/convert_images.php public/uploads/admin/home webp
 *   php scripts/convert_images.php public/uploads/admin/activities jpeg
 */

require_once __DIR__ . '/../init.php';

use App\Helpers\ImageConverter;

// Vérifier si GD est disponible
if (!ImageConverter::isGdAvailable()) {
  echo "❌ Erreur: L'extension GD n'est pas disponible sur ce serveur.\n";
  echo "   Veuillez installer l'extension GD PHP pour utiliser ce script.\n";
  exit(1);
}

// Récupérer les arguments
$directory = isset($argv[1]) ? $argv[1] : 'public/uploads/admin';
$outputFormat = isset($argv[2]) ? $argv[2] : 'webp';

// Normaliser le chemin
$fullPath = realpath(__DIR__ . '/../' . $directory);

if (!$fullPath || !is_dir($fullPath)) {
  echo "❌ Erreur: Le répertoire '$directory' n'existe pas.\n";
  exit(1);
}

if (!in_array($outputFormat, ImageConverter::getOutputFormats())) {
  echo "❌ Erreur: Format de sortie non supporté. Formats acceptés: " . implode(', ', ImageConverter::getOutputFormats()) . "\n";
  exit(1);
}

echo "🔄 Conversion des images du répertoire: $fullPath\n";
echo "   Format de sortie: $outputFormat\n";
echo "────────────────────────────────────────────────────\n";

// Convertir le répertoire
$stats = ImageConverter::convertDirectory($fullPath, $outputFormat);

// Afficher les résultats
echo "\n📊 Résultats de la conversion:\n";
echo "────────────────────────────────────────────────────\n";
echo "  Total fichiers trouvés: {$stats['total']}\n";
echo "  ✅ Convertis avec succès: {$stats['converted']}\n";
echo "  ⏭️  Ignorés (déjà au bon format): {$stats['skipped']}\n";
echo "  ❌ Défaillances: {$stats['failed']}\n";

if (!empty($stats['errors'])) {
  echo "\n⚠️  Erreurs:\n";
  foreach ($stats['errors'] as $error) {
    echo "     • $error\n";
  }
}

echo "────────────────────────────────────────────────────\n";
echo "✨ Conversion terminée!\n";

exit($stats['failed'] > 0 ? 1 : 0);
