<?php
namespace App\Helpers;

/**
 * Helper pour servir les fichiers uploadés et assets de manière sécurisée via asset.php
 */
class AssetHelper
{
  /**
   * Convertit un chemin d'image uploadée en URL sécurisée via asset.php
   * 
   * Ex: '/uploads/admin/home/1234_image.jpg' => 'asset.php?f=admin/home/1234_image.jpg'
   * Ex: 'images/logo.png' => 'asset.php?f=images/logo.png'
   */
  public static function url($path)
  {
    if (empty($path)) {
      return '';
    }

    // Si c'est déjà un chemin absolu à partir de assets ou uploads
    $path = ltrim($path, '/\\');

    // Supprimer le préfixe uploads/ s'il est présent
    if (strpos($path, 'uploads/') === 0) {
      $path = substr($path, 8);
    }

    // Encoder les caractères spéciaux SAUF les slashes (pour préserver la structure du chemin)
    $path = str_replace(' ', '%20', $path);
    $url = 'asset.php?f=' . $path;
    $realPath = self::resolveLocalPath($path);
    if ($realPath) {
      $url .= '&v=' . filemtime($realPath);
    }
    return $url;
  }

  private static function resolveLocalPath($path)
  {
    $path = ltrim(str_replace("..", "", $path), '/\\');

    $assetsBase = realpath(__DIR__ . '/../../assets');
    if ($assetsBase) {
      $assetPath = realpath($assetsBase . '/' . $path);
      if ($assetPath && strpos($assetPath, $assetsBase) === 0 && is_file($assetPath)) {
        return $assetPath;
      }
    }

    $uploadsBase = realpath(__DIR__ . '/../../public/uploads');
    if ($uploadsBase) {
      $uploadPath = realpath($uploadsBase . '/' . $path);
      if ($uploadPath && strpos($uploadPath, $uploadsBase) === 0 && is_file($uploadPath)) {
        return $uploadPath;
      }
    }

    return null;
  }
}
