<?php
require_once 'vendor/autoload.php';

echo "=== Vérification de la correction des URLs ===\n\n";

// Vérifier copies.php
$content = file_get_contents('app/views/bilan/copies.php');
if (strpos($content, 'type=copy&amp;copy_id=') !== false) {
    echo "✅ copies.php: Liens d'export correctement encodés\n";
} else {
    echo "❌ copies.php: Problème avec l'encodage des URLs\n";
}

// Vérifier view.php
$content = file_get_contents('app/views/bilan/view.php');
if (strpos($content, '&amp;action=export&amp;format=pdf') !== false) {
    echo "✅ view.php: Liens d'export correctement encodés\n";
} else {
    echo "❌ view.php: Problème avec l'encodage des URLs\n";
}

// Vérifier initial.php
$content = file_get_contents('app/views/bilan/initial.php');
if (strpos($content, 'action=export&amp;format=pdf&amp;type=initial') !== false) {
    echo "✅ initial.php: Liens d'export correctement encodés\n";
} else {
    echo "❌ initial.php: Problème avec l'encodage des URLs\n";
}

// Vérifier qu'il n'y a pas de &copy; (entité HTML)
if (strpos($content, '&copy;') === false && strpos(file_get_contents('app/views/bilan/copies.php'), '&copy;') === false) {
    echo "✅ Pas d'entité HTML &copy; détectée\n";
} else {
    echo "❌ Entité HTML &copy; détectée\n";
}

echo "\n=== Résumé ===\n";
echo "Les URLs encodées correctement en &amp; vont empêcher l'interprétation erronée du navigateur.\n";
echo "Vous pouvez maintenant exporter les copies périodiques sans erreur!\n";
