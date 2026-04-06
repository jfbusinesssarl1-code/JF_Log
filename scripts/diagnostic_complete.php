#!/usr/bin/env php
<?php
/**
 * DIAGNOSTIC COMPLET - Upload d'activité
 * Exécutez ce script après avoir uploadé une activité via le formulaire admin
 */

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC COMPLET UPLOAD ACTIVITÉ\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$baseDir = dirname(__DIR__) . '/public';

// 1. Vérifier que les dossiers existent
echo "1️⃣  RÉPERTOIRES\n";
$paths = [
    'uploads' => $baseDir . '/uploads',
    'admin' => $baseDir . '/uploads/admin',
    'activities' => $baseDir . '/uploads/admin/activities'
];

foreach ($paths as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    echo "   $name: " . ($exists ? "✅" : "❌") . " $path\n";
    if (!$writable) echo "      ⚠️  PAS WRITABLE!\n";
}

// 2. Lister les fichiers
echo "\n2️⃣  FICHIERS DANS /uploads/admin/activities\n";
$activitiesDir = $baseDir . '/uploads/admin/activities';
$allFiles = glob($activitiesDir . '/*');

if (!$allFiles) {
    echo "   ❌ AUCUN FICHIER!\n";
} else {
    usort($allFiles, function($a, $b) {
        return filemtime($b) - filemtime($a);  // Trier par date décroissante
    });
    
    echo "   Total: " . count($allFiles) . " fichiers\n\n";
    
    echo "   5 DERNIERS FICHIERS (plus récents en premier):\n";
    foreach (array_slice($allFiles, 0, 5) as $file) {
        $name = basename($file);
        $size = is_dir($file) ? '[DIR]' : formatBytes(filesize($file));
        $time = date('Y-m-d H:i:s', filemtime($file));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $icon = match($ext) {
            'webp' => '🎆',
            'jpg', 'jpeg' => '📷',
            'png' => '🖼️',
            default => '📄'
        };
        
        echo "   $icon $time | $size | $name\n";
    }
}

// 3. Vérifier MongoDB (si possible)
echo "\n3️⃣  DONNÉES EN BASE DE DONNÉES (MongoDB)\n";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../app/models/ActivityModel.php';
    
    $activityModel = new \App\Models\ActivityModel();
    $activities = $activityModel->getAll();
    
    if (!$activities) {
        echo "   ❌ Aucune activité en BD\n";
    } else {
        echo "   Total: " . count($activities) . " activités\n\n";
        echo "   5 DERNIÈRES (plus récentes en premier):\n";
        $count = 0;
        foreach ($activities as $activity) {
            if ($count >= 5) break;
            $count++;
            
            $title = $activity['title'] ?? '(sans titre)';
            $image = $activity['image'] ?? null;
            $date = isset($activity['created_at']) 
                ? $activity['created_at']->toDateTime()->format('Y-m-d H:i:s')
                : '(pas de date)';
            
            echo "   📌 $date | $title\n";
            if ($image) {
                $imagePath = $baseDir . $image;
                $imageExists = file_exists($imagePath);
                echo "      Image: $image\n";
                echo "      Fichier: " . ($imageExists ? "✅ EXISTS" : "❌ MISSING") . "\n";
                if ($imageExists) {
                    echo "      Taille: " . formatBytes(filesize($imagePath)) . "\n";
                }
            } else {
                echo "      ⚠️  Pas d'image\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ⚠️  Impossible de vérifier MongoDB: " . $e->getMessage() . "\n";
}

// 4. Afficher les logs si disponibles
echo "\n4️⃣  LOGS IMAGECONVERTER\n";
$logFile = $baseDir . '/uploads/imageconverter.log';
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -10);
    echo "   Dernières 10 entrées du log:\n";
    foreach ($lines as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "   ℹ️  Pas encore de log (fichier créé au premier upload)\n";
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "ℹ️  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════════════\n";
echo "✅ Si vous voyez des fichiers .webp dans 'FICHIERS' → upload fonctionne!\n";
echo "✅ Si vous voyez des images en BD → tout fonctionne!\n";
echo "❌ Si aucun fichier → vérifier:\n";
echo "   1. Avez-vous vraiment cliqué sur 'Ajouter activité'?\n";
echo "   2. Avez-vous sélectionné une image dans le formulaire?\n";
echo "   3. Vérifiez les dossiers sont writable (permissions 755)\n";
echo "═══════════════════════════════════════════════════════════════════\n";

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}
