<?php
namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChain;
use Exception;

/**
 * ImageConverterV2 - Convertit et comprime les images avec Intervention Image + Spatie Optimizer
 * 
 * Bibliothèques utilisées:
 * - intervention/image: Traitement robuste et multi-driver
 * - spatie/image-optimizer: Optimisation sans perte
 */
class ImageConverterV2
{
    private static $maxWidth = 2000;
    private static $maxHeight = 2000;
    private static $uploadDir = __DIR__ . '/../../public/uploads';
    private static $defaultQuality = 80;
    private static $logFile = __DIR__ . '/../../public/uploads/imageconverter.log';
    
    // Tailles max avant compression agressive
    private static $qualityThresholds = [
        5 * 1024 * 1024 => 50,   // > 5 MB: qualité 50
        2 * 1024 * 1024 => 65,   // > 2 MB: qualité 65
        0 => 80                  // < 2 MB: qualité 80
    ];

    /**
     * Log les opérations ImageConverterV2
     */
    private static function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMsg = "[$timestamp] $message\n";
        
        // Écrire dans le fichier log
        if (!is_dir(dirname(self::$logFile))) {
            @mkdir(dirname(self::$logFile), 0755, true);
        }
        @file_put_contents(self::$logFile, $logMsg, FILE_APPEND);
        
        // Aussi log via error_log
        error_log($message);
    }

    /**
     * Convertit une image uploadée avec optimisation maximale
     * Sauvegarde DIRECTEMENT au dossier destination
     * 
     * @param array $fileArray Élément $_FILES
     * @param string $outputDir Répertoire de destination (ex: 'admin/activities')
     * @param string $outputFormat Format de sortie (webp, jpg, png)
     * @return string|false Chemin relatif de l'image convertie ou false
     */
    public static function convertUploadedFile($fileArray, $outputDir = 'admin/home', $outputFormat = 'webp')
    {
        try {
            // 1. Valider l'upload
            if ($fileArray['error'] !== UPLOAD_ERR_OK) {
                self::log('Erreur upload: ' . $fileArray['error']);
                return false;
            }

            $tmpPath = $fileArray['tmp_name'];
            $originalName = basename($fileArray['name']);
            $fileSize = $fileArray['size'] ?? 0;

            if (!file_exists($tmpPath)) {
                self::log('Fichier temporaire introuvable: ' . $tmpPath);
                return false;
            }

            // 2. Créer le répertoire destination
            $fullDir = self::$uploadDir . '/' . $outputDir;
            if (!is_dir($fullDir)) {
                self::log('Création du répertoire: ' . $fullDir);
                if (!@mkdir($fullDir, 0755, true)) {
                    self::log('ERREUR: Impossible de créer ' . $fullDir . ' - vérifier les permissions!');
                    return false;
                }
            }

            // 3. Générer le nom de fichier
            $filename = time() . '_' . md5($originalName . microtime()) . '.' . $outputFormat;
            $outputPath = $fullDir . '/' . $filename;

            // 4. Déterminer la qualité basée sur la taille
            $quality = self::getQualityForSize($fileSize);
            self::log("convertUploadedFile: {$originalName} ({$fileSize} bytes) → qualité {$quality}");

            // 5. Charger l'image avec Intervention
            $manager = new ImageManager(new Driver());
            $image = $manager->read($tmpPath);

            // 7. Redimensionner si nécessaire
            $width = $image->width();
            $height = $image->height();
            
            if ($width > self::$maxWidth || $height > self::$maxHeight) {
                self::log("Redimensionnement {$width}x{$height} → max " . self::$maxWidth . "x" . self::$maxHeight);
                $image->scale(self::$maxWidth, self::$maxHeight);
            }

            // Vérifier que le répertoire est vraiment accessible
            if (!is_writable($fullDir)) {
                self::log('ERREUR: Répertoire ' . $fullDir . ' non accessible en écriture!');
                return false;
            }

            // 8. Sauvegarder l'image (API Intervention 3.x: save avec format: param)
            $image->save($outputPath, format: $outputFormat, quality: $quality);

            // Vérifier que le fichier a été créé
            if (!file_exists($outputPath)) {
                self::log('ERREUR: Fichier non créé à ' . $outputPath . ' après save!');
                return false;
            }

            // 9. Optimiser l'image sauvegardée avec Spatie
            self::optimizeImage($outputPath, $outputFormat);

            // 9. Vérifier le résultat
            $finalSize = filesize($outputPath);
            $reduction = ($fileSize > 0) ? (1 - $finalSize / $fileSize) * 100 : 0;
            
            self::log("Succès! {$originalName} ({$fileSize} bytes) → {$finalSize} bytes ({$reduction}% réduction)");

            return '/uploads/' . $outputDir . '/' . $filename;

        } catch (Exception $e) {
            error_log('ImageConverterV2 Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Détermine la qualité idéale selon la taille du fichier
     */
    private static function getQualityForSize($fileSize)
    {
        foreach (self::$qualityThresholds as $threshold => $quality) {
            if ($fileSize >= $threshold) {
                return $quality;
            }
        }
        return self::$defaultQuality;
    }

    /**
     * Optimise l'image avec les outils appropriés (sans perte)
     * Spatie Image Optimizer utilise des outils externes (jpegoptim, optipng, etc.)
     * Si ces outils ne sont pas disponibles, l'optimisation est skippée silencieusement
     */
    private static function optimizeImage($imagePath, $format)
    {
        try {
            // Utiliser OptimizerChain de Spatie
            // Spatie detecte automatiquement les outils disponibles
            $chain = new OptimizerChain();
            // OptimizerChain détermine automatiquement les optimiseurs basés sur l'extension du fichier
            $chain->optimize($imagePath);
            error_log("ImageConverterV2: Image optimisée: {$imagePath}");
        } catch (Exception $e) {
            // L'optimisation n'est pas critique
            // Les outils externes (jpegoptim, optipng, cwebp) doivent être installés
            // Sur Windows, ils peuvent ne pas être disponibles
            error_log("ImageConverterV2: Optimisation skippée (outils non disponibles): " . $e->getMessage());
        }
    }

    /**
     * Convertit un fichier image en format spécifié
     * Utile pour les conversions batch
     */
    public static function convert($sourcePath, $outputFormat = 'webp', $quality = 80)
    {
        try {
            if (!file_exists($sourcePath)) {
                error_log('ImageConverterV2: Fichier source introuvable: ' . $sourcePath);
                return false;
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($sourcePath);

            // Redimensionner si nécessaire
            if ($image->width() > self::$maxWidth || $image->height() > self::$maxHeight) {
                $image->scale(self::$maxWidth, self::$maxHeight);
            }

            // Obtenir le chemin destination
            $pathinfo = pathinfo($sourcePath);
            $outputPath = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.' . $outputFormat;

            // Sauvegarder avec API Intervention 3.x correcte
            $image->save($outputPath, format: $outputFormat, quality: $quality);

            // Optimiser
            self::optimizeImage($outputPath, $outputFormat);

            return str_replace(realpath(__DIR__ . '/../../public'), '', $outputPath);

        } catch (Exception $e) {
            error_log('ImageConverterV2 Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une image du dossier uploads
     */
    public static function deleteImage($imagePath)
    {
        if (empty($imagePath)) {
            return false;
        }

        try {
            $fullPath = __DIR__ . '/../../public' . $imagePath;
            
            if (file_exists($fullPath) && strpos(realpath($fullPath), realpath(self::$uploadDir)) === 0) {
                if (@unlink($fullPath)) {
                    error_log('ImageConverterV2: Image supprimée: ' . $fullPath);
                    return true;
                }
            }
        } catch (Exception $e) {
            error_log('ImageConverterV2 Exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Supprime plusieurs images
     */
    public static function deleteImages(array $imagePaths)
    {
        $deleted = 0;
        foreach ($imagePaths as $path) {
            if (self::deleteImage($path)) {
                $deleted++;
            }
        }
        return $deleted;
    }
}
