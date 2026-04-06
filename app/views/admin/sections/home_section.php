<style>
.carousel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 24px;
    margin-top: 30px;
}

.carousel-item-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
}

.carousel-item-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.carousel-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
}

.carousel-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.carousel-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 64px;
}

.carousel-content {
    padding: 24px;
}

.carousel-title {
    font-size: 20px;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
    line-height: 1.3;
}

.carousel-description {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.carousel-link {
    font-size: 13px;
    color: #1a5490;
    margin-bottom: 16px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.carousel-actions {
    display: flex;
    gap: 8px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

.modern-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.modern-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f0f0;
}

.modern-card-header i {
    font-size: 28px;
    color: #1a5490;
}

.modern-card-header h5 {
    margin: 0;
    font-weight: 600;
}

.preview-image-home {
    max-width: 100%;
    max-height: 150px;
    border-radius: 8px;
    margin-top: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>

<div class="section-header">
  <h1><i class="bi bi-images"></i> Carrousel Page d'Accueil</h1>
  <p>Gérez les diapositives du carrousel de votre page d'accueil</p>
</div>

<?php use App\Helpers\AssetHelper; ?>

<div class="modern-card">
  <div class="modern-card-header">
    <i class="bi bi-<?php echo !empty($editItem) ? 'pencil-square' : 'plus-circle-fill'; ?>"></i>
    <h5><?php echo !empty($editItem) ? 'Modifier la diapositive' : 'Créer une nouvelle diapositive'; ?></h5>
  </div>
  
  <form method="post" enctype="multipart/form-data" action="?page=admin&section=home">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
    <?php if (!empty($editItem)): ?>
      <input type="hidden" name="item_id" value="<?= htmlspecialchars((string) ($editItem['_id'] ?? '')) ?>">
    <?php endif; ?>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label for="homeTitle" class="form-label fw-semibold">
          <i class="bi bi-text-left"></i> Titre <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" id="homeTitle" name="title" 
               placeholder="Ex: Bienvenue chez CB.JF"
               value="<?= htmlspecialchars($editItem['title'] ?? '') ?>"
               required>
      </div>
      <div class="col-md-6">
        <label for="homeLink" class="form-label fw-semibold">
          <i class="bi bi-link-45deg"></i> Lien (optionnel)
        </label>
        <input type="text" class="form-control" id="homeLink" name="link" 
               placeholder="#section-id ou https://..."
               value="<?= htmlspecialchars($editItem['link'] ?? '') ?>">
      </div>
    </div>

    <div class="mb-3">
      <label for="homeDescription" class="form-label fw-semibold">
        <i class="bi bi-text-paragraph"></i> Description
      </label>
      <textarea class="form-control" id="homeDescription" name="description" rows="3"
        placeholder="Description qui s'affichera sur la diapositive..."><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-4">
      <label for="homeImage" class="form-label fw-semibold">
        <i class="bi bi-image"></i> Image de bannière
        <?php echo empty($editItem) ? '' : ' (laisser vide pour garder l\'actuelle)'; ?>
      </label>
      <input type="file" class="form-control" id="homeImage" name="image" accept="image/*"
             onchange="previewHomeImage(this)">
      <small class="text-muted">Format recommandé: 1920x800px - JPG, PNG, WebP - Max 5 Mo</small>
      
      <?php if (!empty($editItem) && !empty($editItem['image'])): ?>
        <div class="mt-3">
          <p class="mb-2 text-muted small">Image actuelle:</p>
          <img src="<?= AssetHelper::url($editItem['image']); ?>" 
               class="preview-image-home" alt="Aperçu">
        </div>
      <?php endif; ?>
      
      <div id="imagePreviewHome" class="mt-3" style="display: none;">
        <p class="mb-2 text-muted small">Nouvel aperçu:</p>
        <img id="previewImgHome" class="preview-image-home" alt="Aperçu">
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-lg px-4">
        <i class="bi bi-<?php echo !empty($editItem) ? 'check-circle' : 'plus-circle'; ?>"></i>
        <?php echo !empty($editItem) ? 'Enregistrer' : 'Créer'; ?>
      </button>
      <?php if (!empty($editItem)): ?>
        <a href="?page=admin&section=home" class="btn btn-secondary btn-lg px-4">
          <i class="bi bi-x-circle"></i> Annuler
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="modern-card">
  <div class="modern-card-header">
    <i class="bi bi-grid-3x3-gap-fill"></i>
    <h5>Diapositives du carrousel (<?php echo count($homeItems ?? []); ?>)</h5>
  </div>
  
  <?php if (!empty($homeItems)): ?>
    <div class="carousel-grid">
      <?php foreach ($homeItems as $item):
        $itemIdStr = (string) ($item['_id'] ?? '');
      ?>
        <div class="carousel-item-card">
          <div class="carousel-image">
            <?php if (!empty($item['image'])): ?>
              <img src="<?php echo AssetHelper::url($item['image']); ?>"
                   alt="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
            <?php else: ?>
              <div class="carousel-placeholder">
                <i class="bi bi-image"></i>
              </div>
            <?php endif; ?>
          </div>
          
          <div class="carousel-content">
            <h3 class="carousel-title"><?php echo htmlspecialchars($item['title'] ?? 'Sans titre'); ?></h3>
            
            <?php if (!empty($item['description'])): ?>
              <p class="carousel-description">
                <?php echo htmlspecialchars($item['description']); ?>
              </p>
            <?php endif; ?>
            
            <?php if (!empty($item['link'])): ?>
              <div class="carousel-link">
                <i class="bi bi-link-45deg"></i> <?php echo htmlspecialchars($item['link']); ?>
              </div>
            <?php endif; ?>
            
            <div class="carousel-actions">
              <a href="?page=admin&section=home&action=edit&item_id=<?= urlencode($itemIdStr) ?>"
                 class="btn btn-warning flex-fill">
                <i class="bi bi-pencil-fill"></i> Modifier
              </a>
              <a href="?page=admin&section=home&action=delete&item_id=<?= urlencode($itemIdStr) ?>&token=<?= urlencode(\App\Core\Csrf::getToken()) ?>"
                 class="btn btn-danger flex-fill"
                 onclick="return confirm('⚠️ Supprimer cette diapositive ?');">
                <i class="bi bi-trash-fill"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-5">
      <i class="bi bi-images" style="font-size: 80px; color: #ddd;"></i>
      <p class="text-muted mt-3">Aucune diapositive pour le moment</p>
      <p class="text-muted" style="font-size: 14px;">Créez votre premier élément de carrousel ci-dessus</p>
    </div>
  <?php endif; ?>
</div>

<script>
function previewHomeImage(input) {
    const preview = document.getElementById('imagePreviewHome');
    const previewImg = document.getElementById('previewImgHome');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>