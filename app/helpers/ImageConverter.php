<?php
namespace App\Helpers;

use Exception;

/**
 * ImageConverter - Convertit et optimise les images pour les navigateurs
 * 
 * Formats supportés en sortie: JPEG, PNG, WebP, GIF
 * Formats acceptés en entrée: JPEG, PNG, GIF, BMP, WebP, TIFF, et autres (via conversion)
 */
class ImageConverter
{
  private static $supportedFormats = ['jpeg', 'jpg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif'];
  private static $outputFormats = ['jpeg', 'png', 'webp', 'gif'];
  private static $maxWidth = 2000;
  private static $maxHeight = 2000;
  private static $uploadDir = __DIR__ . '/../../public/uploads';

  /**
   * Convertit une image uploadée au format optimal pour les navigateurs
   * 
   * @param string $sourcePath Chemin du fichier source
   * @param string $outputFormat Format de sortie (jpeg, png, webp, gif)
   * @param int $quality Qualité de compression (0-100), défaut 75 pour compression plus agressive
   * @return string|false Chemin du fichier converti ou false en cas d'erreur
   */
  public static function convert($sourcePath, $outputFormat = 'jpeg', $quality = 75)
  {
    // Vérifier que le fichier existe
    if (!file_exists($sourcePath)) {
      error_log('ImageConverter: Fichier source introuvable: ' . $sourcePath);
      return false;
    }

    // Déterminer le format d'entrée
    $sourceFormat = self::detectFormat($sourcePath);
    if (!$sourceFormat) {
      error_log('ImageConverter: Format de fichier non supporté: ' . $sourcePath);
      return false;
    }

    // Normaliser le format de sortie
    $outputFormat = strtolower($outputFormat);
    if (!in_array($outputFormat, self::$outputFormats)) {
      $outputFormat = 'jpeg';
    }

    try {
      // Charger l'image source
      $image = self::loadImage($sourcePath, $sourceFormat);
      if (!$image) {
        error_log('ImageConverter: Impossible de charger l\'image: ' . $sourcePath);
        return false;
      }

      // Redimensionner si nécessaire
      $image = self::resizeIfNeeded($image);

      // Générer le chemin de sortie
      $pathinfo = pathinfo($sourcePath);
      $outputPath = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.' . $outputFormat;

      // Sauvegarder l'image convertie
      if (!self::saveImage($image, $outputPath, $outputFormat, $quality)) {
        error_log('ImageConverter: Impossible de sauvegarder l\'image convertie: ' . $outputPath);
        imagedestroy($image);
        return false;
      }

      // Libérer la mémoire
      imagedestroy($image);

      // Retourner le chemin relatif
      return str_replace(realpath(__DIR__ . '/../../public'), '', $outputPath);
    } catch (Exception $e) {
      error_log('ImageConverter Exception: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Détecte le format de l'image en utilisant les fonctions PHP
   * 
   * @param string $filePath Chemin du fichier
   * @return string|false Format détecté ou false
   */
  private static function detectFormat($filePath)
  {
    // Vérifier avec getimagesize d'abord (le plus fiable)
    $imageInfo = @getimagesize($filePath);
    if ($imageInfo !== false) {
      // getimagesize retourne le type MIME
      $mimeType = $imageInfo['mime'] ?? '';
      $mapping = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/bmp' => 'bmp',
        'image/tiff' => 'tiff',
        'image/x-windows-bmp' => 'bmp',
      ];
      if (isset($mapping[$mimeType])) {
        return $mapping[$mimeType];
      }
    }

    // Fallback: vérifier l'extension
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (in_array($ext, self::$supportedFormats)) {
      return str_replace('jpg', 'jpeg', $ext);
    }

    return false;
  }

  /**
   * Charge une image en ressource GD
   * 
   * @param string $filePath Chemin du fichier
   * @param string $format Format détecté
   * @return resource|false Ressource image ou false
   */
  private static function loadImage($filePath, $format)
  {
    switch ($format) {
      case 'jpeg':
        return @imagecreatefromjpeg($filePath);
      case 'png':
        return @imagecreatefrompng($filePath);
      case 'gif':
        return @imagecreatefromgif($filePath);
      case 'webp':
        return @imagecreatefromwebp($filePath);
      case 'bmp':
        return @imagecreatefrombmp($filePath);
      case 'tiff':
      case 'tif':
        // TIFF support requires libtiff extension which is not always available
        // Return false to skip TIFF processing
        if (function_exists('imagecreatefromtiff')) {
          return @imagecreatefromtiff($filePath);
        }
        return false;
      default:
        return false;
    }
  }

  /**
   * Redimensionne l'image si elle dépasse les dimensions maximales
   * 
   * @param resource $image Ressource image GD
   * @return resource Image redimensionnée
   */
  private static function resizeIfNeeded($image)
  {
    $width = imagesx($image);
    $height = imagesy($image);

    // Vérifier si redimensionnement nécessaire
    if ($width <= self::$maxWidth && $height <= self::$maxHeight) {
      return $image;
    }

    // Calculer les nouvelles dimensions (respecter le ratio)
    $ratio = $width / $height;
    if ($width > $height) {
      $newWidth = self::$maxWidth;
      $newHeight = intval(self::$maxWidth / $ratio);
    } else {
      $newHeight = self::$maxHeight;
      $newWidth = intval(self::$maxHeight * $ratio);
    }

    // Créer l'image redimensionnée
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Copier et redimensionner
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagedestroy($image);

    return $newImage;
  }

  /**
   * Sauvegarde l'image en format spécifié
   * 
   * @param resource $image Ressource image GD
   * @param string $outputPath Chemin de sortie
   * @param string $format Format de sortie
   * @param int $quality Qualité (0-100)
   * @return bool Succès de la sauvegarde
   */
  private static function saveImage($image, $outputPath, $format, $quality)
  {
    // Assurer que le répertoire existe
    $dir = dirname($outputPath);
    if (!is_dir($dir)) {
      @mkdir($dir, 0755, true);
    }

    switch ($format) {
      case 'jpeg':
        // Augmenter la compression pour réduire la taille (70% de qualité)
        return @imagejpeg($image, $outputPath, max(70, $quality - 10));
      case 'png':
        // PNG: compression maximale (9 = maximum)
        $compression = 9;
        return @imagepng($image, $outputPath, $compression);
      case 'gif':
        return @imagegif($image, $outputPath);
      case 'webp':
        // WebP: réduire la qualité pour compression agressive (65-75)
        $webpQuality = max(65, $quality - 10);
        return @imagewebp($image, $outputPath, $webpQuality);
      default:
        return false;
    }
  }

  /**
   * Convertit une image uploadée via formulaire
   * Compresse agressivement si le fichier est volumineux
   * Sauvegarde DIRECTEMENT au dossier destination (pas de fichier temporaire)
   * 
   * @param array $fileArray Élément $_FILES
   * @param string $outputDir Répertoire de destination
   * @param string $outputFormat Format de sortie
   * @return string|false Chemin relatif de l'image convertie ou false
   */
  public static function convertUploadedFile($fileArray, $outputDir = 'admin/home', $outputFormat = 'webp')
  {
    // Vérifier les erreurs d'upload
    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
      error_log('ImageConverter: Erreur lors de l\'upload: ' . $fileArray['error']);
      return false;
    }

    $tmpPath = $fileArray['tmp_name'];
    $originalName = basename($fileArray['name']);
    $fileSize = $fileArray['size'] ?? 0;

    // Vérifier que c'est une image
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
      error_log('ImageConverter: Le fichier uploadé n\'est pas une image valide');
      return false;
    }

    // Déterminer le format d'entrée
    $sourceFormat = self::detectFormat($tmpPath);
    if (!$sourceFormat) {
      error_log('ImageConverter: Format de fichier non supporté: ' . $tmpPath);
      return false;
    }

    // Créer le répertoire de destination
    $fullDir = self::$uploadDir . '/' . $outputDir;
    if (!is_dir($fullDir)) {
      @mkdir($fullDir, 0755, true);
    }

    // Générer un nom de fichier unique
    $filename = time() . '_' . md5($originalName . microtime()) . '.' . $outputFormat;
    $outputPath = $fullDir . '/' . $filename;

    // Choisir la qualité basée sur la taille du fichier
    $quality = 75; // Défaut
    if ($fileSize > 5 * 1024 * 1024) {
      // > 5MB: compression très agressive
      $quality = 60;
      error_log('ImageConverter: Fichier volumineux (' . round($fileSize / 1024 / 1024, 2) . ' MB), compression très agressive');
    } elseif ($fileSize > 2 * 1024 * 1024) {
      // > 2MB: compression agressive
      $quality = 65;
      error_log('ImageConverter: Fichier modérément volumineux (' . round($fileSize / 1024 / 1024, 2) . ' MB), compression augmentée');
    }

    try {
      // Charger l'image source directement depuis l'upload
      $image = self::loadImage($tmpPath, $sourceFormat);
      if (!$image) {
        error_log('ImageConverter: Impossible de charger l\'image uploadée: ' . $tmpPath);
        return false;
      }

      // Redimensionner si nécessaire
      $image = self::resizeIfNeeded($image);

      // Sauvegarder DIRECTEMENT au dossier destination (PAS de fichier temporaire)
      if (!self::saveImage($image, $outputPath, $outputFormat, $quality)) {
        error_log('ImageConverter: Impossible de sauvegarder l\'image: ' . $outputPath);
        imagedestroy($image);
        return false;
      }

      // Libérer la mémoire
      imagedestroy($image);

      // Vérifier la taille finale
      $finalSize = filesize($outputPath);
      error_log('ImageConverter: Image uploadée - Taille originale: ' . round($fileSize / 1024 / 1024, 2) . ' MB → ' . round($finalSize / 1024, 2) . ' KB');

      // Retourner le chemin relatif
      return '/uploads/' . $outputDir . '/' . $filename;

    } catch (Exception $e) {
      error_log('ImageConverter Exception: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Convertit en masse les images d'un répertoire
   * (utile pour migrer les anciens uploads)
   * 
   * @param string $sourceDir Répertoire source
   * @param string $outputFormat Format de sortie
   * @return array Statistiques de conversion
   */
  public static function convertDirectory($sourceDir, $outputFormat = 'webp')
  {
    $stats = [
      'total' => 0,
      'converted' => 0,
      'failed' => 0,
      'skipped' => 0,
      'errors' => []
    ];

    if (!is_dir($sourceDir)) {
      $stats['errors'][] = 'Répertoire source introuvable: ' . $sourceDir;
      return $stats;
    }

    $files = glob($sourceDir . '/*');
    foreach ($files as $file) {
      if (is_file($file)) {
        $stats['total']++;
        $format = self::detectFormat($file);

        if (!$format) {
          $stats['skipped']++;
          continue;
        }

        if ($format === $outputFormat) {
          $stats['skipped']++;
          continue;
        }

        if (self::convert($file, $outputFormat) !== false) {
          $stats['converted']++;
        } else {
          $stats['failed']++;
          $stats['errors'][] = 'Échec de conversion: ' . basename($file);
        }
      }
    }

    return $stats;
  }

  /**
   * Obtient les formats supportés
   * 
   * @return array Formats supportés
   */
  public static function getSupportedFormats()
  {
    return self::$supportedFormats;
  }

  /**
   * Obtient les formats de sortie disponibles
   * 
   * @return array Formats de sortie
   */
  public static function getOutputFormats()
  {
    return self::$outputFormats;
  }

  /**
   * Vérifie si GD est disponible
   * 
   * @return bool GD disponible
   */
  public static function isGdAvailable()
  {
    return extension_loaded('gd') && function_exists('gd_info');
  }

  /**
   * Vérifie si WebP est supporté
   * 
   * @return bool WebP supporté
   */
  public static function isWebpSupported()
  {
    return function_exists('imagewebp');
  }

  /**
   * Supprime un fichier image du dossier uploads
   * 
   * @param string $imagePath Chemin relatif de l'image (ex: '/uploads/admin/home/filename.webp')
   * @return bool True si supprimé, false sinon
   */
  public static function deleteImage($imagePath)
  {
    if (empty($imagePath)) {
      return false;
    }

    try {
      // Convertir le chemin relatif en chemin absolu
      $fullPath = __DIR__ . '/../../public' . $imagePath;
      
      // Vérifier que le fichier existe et qu'il est dans le répertoire uploads
      if (file_exists($fullPath) && strpos(realpath($fullPath), realpath(self::$uploadDir)) === 0) {
        if (@unlink($fullPath)) {
          error_log('ImageConverter: Image supprimée: ' . $fullPath);
          return true;
        } else {
          error_log('ImageConverter: Impossible de supprimer l\'image: ' . $fullPath);
          return false;
        }
      }
    } catch (Exception $e) {
      error_log('ImageConverter Exception lors de la suppression: ' . $e->getMessage());
    }

    return false;
  }

  /**
   * Supprime plusieurs fichiers images
   * 
   * @param array $imagePaths Tableau de chemins relatifs d'images
   * @return int Nombre d'images supprimées avec succès
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
