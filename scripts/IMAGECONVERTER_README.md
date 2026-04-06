# 📸 ImageConverter - Algorithme de Conversion et Optimisation d'Images

## Vue d'ensemble

L'`ImageConverter` est une classe PHP complète pour convertir et optimiser les images pour l'affichage dans les navigateurs. Elle gère automatiquement :

✅ **Détection de format** - Identifie le format réel de l'image (JPEG, PNG, GIF, WebP, BMP, TIFF)
✅ **Conversion** - Convertit vers les formats supportés par les navigateurs  
✅ **Redimensionnement** - Réduit les images trop grandes
✅ **Compression** - Optimise la taille de fichier
✅ **Sécurité** - Valide que ce sont des images valides

## Architecture Technique

### Classes Principales

#### `App\Helpers\ImageConverter`
Classe utilitaire statique pour la conversion d'images.

**Méthodes principales:**

```php
// Convertir une image
ImageConverter::convert($sourcePath, $outputFormat, $quality);

// Convertir un fichier uploadé
ImageConverter::convertUploadedFile($fileArray, $outputDir, $outputFormat);

// Convertir un répertoire entier
ImageConverter::convertDirectory($sourceDir, $outputFormat);

// Vérifier la disponibilité de GD
ImageConverter::isGdAvailable();

// Vérifier le support WebP
ImageConverter::isWebpSupported();
```

## Algorithme de Conversion Détaillé

### 1. **Détection du Format**
```
INPUT: Fichier image
  ↓
├─ Vérifier avec getimagesize() [le plus fiable]
│  ├─ Extraire le MIME type
│  └─ Mapper vers format interne
├─ FALLBACK: Vérifier l'extension du fichier
└─ RETOUR: Format détecté ou FALSE
```

**Format supportés détectés:**
- `image/jpeg` → `jpeg`
- `image/png` → `png`
- `image/gif` → `gif`
- `image/webp` → `webp`
- `image/bmp` → `bmp` (Windows Bitmap)
- `image/tiff` → `tiff` (Tag Image File Format)

### 2. **Chargement de l'Image**
```
FORMAT DÉTECTÉ
  ↓
imagecreatefrom[FORMAT]()
  ├─ imagecreatefromjpeg()
  ├─ imagecreatefrompng()
  ├─ imagecreatefromgif()
  ├─ imagecreatefromwebp()
  ├─ imagecreatefrombmp()
  └─ imagecreatefromtiff()
```

**Ressource GD:** Les fonctions retournent une ressource d'image GD que PHP peut manipuler.

### 3. **Redimensionnement Intelligent**
```
RESPECTER LE RATIO D'ASPECT
  ↓
SI largeur > hauteur:
  ├─ nouvelle_largeur = 2000px
  └─ nouvelle_hauteur = 2000 / (original_width/original_height)
SINON:
  ├─ nouvelle_hauteur = 2000px
  └─ nouvelle_largeur = 2000 * (original_width/original_height)
  ↓
imagecopyresampled() - Redimensionner avec qualité
  ↓
imagedestroy() - Libérer la mémoire
```

**Optimisation:** 
- Les images > 2000x2000 sont redimensionnées
- Le ratio d'aspect est toujours préservé
- Pas de distorsion garantie

### 4. **Compression et Sauvegarde**
```
FORMAT DE SORTIE choisi
  ↓
JPEG:
  ├─ Quality: 0-100 (85 par défaut)
  ├─ Meilleur comprmise taille/qualité
  └─ imagejpeg($image, $path, $quality)
  
PNG:
  ├─ Compression: 0-9 (converti depuis quality)
  ├─ Pas de perte (lossless)
  └─ imagepng($image, $path, $compression)
  
WebP:
  ├─ Quality: 0-100 (meilleur compression)
  ├─ Moderne, petit fichier
  └─ imagewebp($image, $path, $quality)
  
GIF:
  ├─ Max 256 couleurs
  ├─ Support transparent et animé
  └─ imagegif($image, $path)
```

## Flux d'Utilisation

### Usage 1: Upload avec Conversion Automatique

```php
use App\Helpers\ImageConverter;

// Dans votre formulaire, traiter le fichier uploadé
if (!empty($_FILES['image'])) {
    // Convertir automatiquement en WebP optimisé
    $imagePath = ImageConverter::convertUploadedFile(
        $_FILES['image'],
        'admin/home',        // Répertoire de destination
        'webp'               // Format de sortie
    );
    
    if ($imagePath !== false) {
        $item['image'] = $imagePath;  // Ex: /uploads/admin/home/1707...webp
    }
}
```

**Résultat:**
- ✅ Image convertie en WebP
- ✅ Redimensionnée si trop grande
- ✅ Compressée à qualité 85
- ✅ Chemin retourné prêt pour base de données

### Usage 2: Conversion d'Existant

```php
// Convertir une image unique
$result = ImageConverter::convert(
    '/absolute/path/to/image.bmp',
    'jpeg',    // Convertir BMP en JPEG
    90         // Qualité 90
);

if ($result !== false) {
    echo "Image convertie: " . $result;
}
```

### Usage 3: Batch - Convertir un Répertoire

```php
// Script CLI - Convertir toutes les images du répertoire en WebP
$stats = ImageConverter::convertDirectory(
    '/path/to/uploads/admin/home',
    'webp'
);

// Résultats:
// $stats['total']     - Fichiers trouvés
// $stats['converted'] - Convertis avec succès
// $stats['skipped']   - Déjà au bon format
// $stats['failed']    - Erreurs
// $stats['errors']    - Détails des erreurs
```

## Scripts CLI

### Convertir des Images en Batch

```bash
# Convertir un répertoire spécifique en WebP
php scripts/convert_images.php public/uploads/admin/home webp

# Convertir les activités en JPEG
php scripts/convert_images.php public/uploads/admin/activities jpeg

# Convertir tous les uploads en WebP
php scripts/convert_images.php public/uploads/admin webp
```

**Sortie:**
```
🔄 Conversion des images du répertoire: /full/path/uploads/admin/home
   Format de sortie: webp
────────────────────────────────────────────────────
📊 Résultats de la conversion:
────────────────────────────────────────────────────
  Total fichiers trouvés: 15
  ✅ Convertis avec succès: 12
  ⏭️  Ignorés (déjà au bon format): 2
  ❌ Défaillances: 1

⚠️  Erreurs:
     • Échec de conversion: old-image.bmp

────────────────────────────────────────────────────
✨ Conversion terminée!
```

## Intégration dans les Contrôleurs

L'`AdminController` a été mis à jour pour utiliser automatiquement ImageConverter:

### Avant (sans optimisation)
```php
move_uploaded_file($tmp, $dest);
$item['image'] = '/uploads/admin/home/' . $name;  // Format original, taille énorme
```

### Après (avec OptImageConverter)
```php
$imagePath = ImageConverter::convertUploadedFile(
    $_FILES['image'], 
    'admin/home', 
    'webp'
);

if ($imagePath === false) {
    // Fallback au format original
    move_uploaded_file($tmp, $dest);
    $item['image'] = '/uploads/admin/home/' . $name;
} else {
    $item['image'] = $imagePath;  // Format WebP optimisé
}
```

## Formats Supportés

### Formats d'Entrée (Détection)
| Format | Extension | MIME Type | Support |
|--------|-----------|-----------|---------|
| JPEG | .jpg, .jpeg | image/jpeg | ✅ |
| PNG | .png | image/png | ✅ |
| GIF | .gif | image/gif | ✅ |
| WebP | .webp | image/webp | ✅ |
| BMP | .bmp | image/bmp | ✅ |
| TIFF | .tif, .tiff | image/tiff | ✅ |

### Formats de Sortie (Conversion)
| Format | Avantages | Meilleur pour |
|--------|-----------|---------------|
| **JPEG** | Petit fichier, bonne qualité | Photos, images complexes |
| **PNG** | Sans perte, transparent | Logos, designs graphiques |
| **WebP** | Très compact, moderne | Web moderne, tous usages |
| **GIF** | Animé, transparent | Animations simples |

## Optimisations et Performance

### Compression par Défaut
- **Qualité JPEG:** 85/100 (bon équilibre)
- **Compression PNG:** 5/9 (bon compromis vitesse/compression)
- **Qualité WebP:** 85/100 (très compact)

### Redimensionnement
- **Dimension max:** 2000x2000 px
- **Algorithme:** Resampling Lanczos (meilleure qualité)
- **Ratio:** Préservé (pas de déformation)

### Exemple de Réduction de Taille:
```
Image originale BMP: 15 MB
        ↓ (détection format)
    Conversion JPEG 85: 450 KB (-97%)
        ↓ (ou)
    Conversion WebP 85: 280 KB (-98%)
```

## Gestion des Erreurs

### Cas de Défaillance
1. **Format non supporté** → Retour FALSE, log d'erreur
2. **Fichier corrompu** → getimagesize() échoue, conversion ignorée
3. **Pas de GD disponible** → Erreur au log
4. **Problème écriture disque** → Fallback au format original

### Logs
Tous les erreurs sont consignées dans le fichier PHP error_log:
```
ImageConverter: Format de fichier non supporté: /path/to/image.raw
ImageConverter: Impossible de charger l'image: /path/to/corrupted.jpg
ImageConverter Exception: Erreur lors de la sauvegarde
```

## Exemple Complet: Diagnostic

Créer un script pour identifier les images problématiques:

```php
<?php
use App\Helpers\ImageConverter;

// Vérifier la disponibilité de GD
if (!ImageConverter::isGdAvailable()) {
    die('❌ GD n\'est pas disponible');
}

echo "✅ GD disponible\n";
echo "✅ WebP supporté: " . (ImageConverter::isWebpSupported() ? 'OUI' : 'NON') . "\n\n";

// Tester chaque répertoire
$dirs = [
    'public/uploads/admin/home',
    'public/uploads/admin/activities',
    'public/uploads/admin/about',
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $stats = ImageConverter::convertDirectory($dir, 'webp');
        echo "📁 $dir\n";
        echo "   Trouvés: {$stats['total']} | Convertis: {$stats['converted']} | Échoués: {$stats['failed']}\n";
    }
}

?>
```

## Checklist de Configuration

- [ ] PHP extensions GD activée
- [ ] Répertoire `public/uploads/` accessible en écriture
- [ ] ImageConverter.php dans `app/helpers/`
- [ ] AdminController mis à jour
- [ ] script `convert_images.php` dans `scripts/`
- [ ] AssetHelper.php utilise les images converties
- [ ] asset.php sert depuis `/uploads/`

## Troubleshooting

### "GD extension not available"
```bash
# Linux/Ubuntu
sudo apt-get install php-gd

# macOS
brew install php@8.1 --with-gd

# Vérifier
php -m | grep gd
```

### Images ne s'affichent pas après conversion
1. Vérifier les permissions: `chmod 755 public/uploads/`
2. Vérifier les chemins retournés par convertUploadedFile
3. Vérifier que asset.php sert les fichiers: `<?php echo htmlspecialchars(AssetHelper::url('/uploads/admin/home/image.webp')); ?>`

### OutOfMemory lors de grandes images
- Augmenter memory_limit: `php.ini` → `memory_limit = 256M`
- Ou décrémenter maxWidth/maxHeight dans ImageConverter

---

**Version:** 1.0  
**Date:** 2026-02-14  
**Auteur:** ImageConverter Helper
