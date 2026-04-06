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
    return 'asset.php?f=' . $path;
  }
}
