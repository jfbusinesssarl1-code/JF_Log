<div class="section-header">
  <h1><i class="bi bi-file-text"></i> Section À propos</h1>
  <p>Gérez les informations complètes de la section À propos avec sections multiples et images</p>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= htmlspecialchars($success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <?= htmlspecialchars($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="admin-card p-4 mb-4">
  <h5 class="mb-4">
    <?php echo !empty($editItem) ? 'Modifier l\'article À propos' : 'Créer un nouvel article À propos'; ?>
  </h5>
  <form method="post" enctype="multipart/form-data" action="?page=admin&section=about">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
    <?php if (!empty($editItem)): ?>
    <input type="hidden" name="item_id" value="<?= htmlspecialchars((string) ($editItem['_id'] ?? '')) ?>">
    <?php endif; ?>

    <!-- TITRE PRINCIPAL ET TEXTE PRINCIPAL -->
    <div class="mb-4">
      <h6 class="mb-3"><i class="bi bi-pencil-square"></i> Informations principales</h6>
      <div class="row mb-3">
        <div class="col-md-12">
          <label for="aboutTitle" class="form-label">Titre principal *</label>
          <input type="text" class="form-control" id="aboutTitle" name="aboutTitle"
            placeholder="ex: À propos de J.F Business" required
            value="<?= htmlspecialchars($editItem['title'] ?? '') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label for="aboutText" class="form-label">Contenu texte principal *</label>
        <textarea class="form-control" id="aboutText" name="aboutText" rows="5"
          placeholder="Texte principal qui s'affichera sur la page d'accueil"
          required><?= htmlspecialchars($editItem['text'] ?? '') ?></textarea>
        <small class="text-muted">Conseil: gardez-le concis, le reste sera dans &quot;Voir plus&quot;</small>
      </div>
    </div>

    <!-- SECTIONS MULTIPLES -->
    <div class="mb-4">
      <h6 class="mb-3"><i class="bi bi-layout-text-sidebar-reverse"></i> Sections supplémentaires</h6>
      <p class="text-muted small">Ajoutez des sections comme "Notre Mission", "Notre Vision", etc.</p>

      <div id="sectionsContainer">
        <?php
                $sections = $editItem['sections'] ?? [];
                if (!empty($sections)) {
                    foreach ($sections as $idx => $section):
                        ?>
        <div class="section-item card mb-3 p-3" data-index="<?= $idx ?>">
          <button type="button" class="btn btn-sm btn-danger float-end remove-section">
            <i class="bi bi-trash"></i> Supprimer
          </button>
          <div class="mb-3">
            <label class="form-label">Sous-titre</label>
            <input type="text" class="form-control section-subtitle" placeholder="ex: Notre Mission"
              value="<?= htmlspecialchars($section['subtitle'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Texte</label>
            <textarea class="form-control section-text" rows="3" placeholder="Contenu de cette section"></textarea>
            <script>
            document.currentScript.previousElementSibling.value = <?= json_encode($section['text'] ?? '') ?>;
            </script>
          </div>
        </div>
        <?php
                    endforeach;
                }
                ?>
      </div>

      <button type="button" class="btn btn-outline-primary mb-3" id="addSectionBtn">
        <i class="bi bi-plus-circle"></i> Ajouter une section
      </button>
    </div>

    <!-- IMAGES CAROUSEL -->
    <div class="mb-4">
      <h6 class="mb-3"><i class="bi bi-images"></i> Images pour le carrousel</h6>
      <p class="text-muted small">Ajoutez plusieurs images qui défileront dans le carrousel de droite</p>

      <div id="imagesContainer">
        <?php
                $images = $editItem['images'] ?? [];
                if (!empty($images)) {
                    foreach ($images as $idx => $img):
                        ?>
        <div class="image-item card mb-3 p-3" data-index="<?= $idx ?>">
          <button type="button" class="btn btn-sm btn-danger float-end remove-image">
            <i class="bi bi-trash"></i> Supprimer
          </button>
          <div class="mb-3">
            <label class="form-label">Image &nbsp;
              <?php if (!empty($img)): ?>
              <img src="<?= htmlspecialchars($img) ?>" style="max-height: 40px; margin-left: 10px;">
              <?php endif; ?>
            </label>
            <div class="input-group">
              <input type="hidden" class="image-url" value="<?= htmlspecialchars($img ?? '') ?>">
              <input type="file" class="form-control image-file" name="aboutImage[]" accept="image/*">
              <span class="input-group-text text-muted small">ou gardez l'actuelle</span>
            </div>
          </div>
        </div>
        <?php
                    endforeach;
                }
                ?>
      </div>

      <button type="button" class="btn btn-outline-primary mb-3" id="addImageBtn">
        <i class="bi bi-plus-circle"></i> Ajouter une image
      </button>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-<?php echo !empty($editItem) ? 'pencil' : 'plus-circle'; ?>"></i>
        <?php echo !empty($editItem) ? 'Modifier' : 'Créer'; ?>
      </button>
      <?php if (!empty($editItem)): ?>
      <a href="?page=admin&section=about" class="btn btn-secondary">
        <i class="bi bi-x-circle"></i> Annuler
      </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- ARTICLES EXISTANTS -->
<div class="admin-card p-4">
  <h5 class="mb-4">Articles À propos existants</h5>
  <?php if (!empty($aboutItems)): ?>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>Titre</th>
          <th>Texte principal</th>
          <th>Sections</th>
          <th>Images</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($aboutItems as $item):
                        $itemIdStr = (string) ($item['_id'] ?? '');
                        ?>
        <tr>
          <td>
            <strong><?php echo htmlspecialchars($item['title'] ?? 'Sans titre'); ?></strong>
          </td>
          <td>
            <?php echo htmlspecialchars(substr($item['text'] ?? '', 0, 50)); ?>
            <?php echo (strlen($item['text'] ?? '') > 50) ? '...' : ''; ?>
          </td>
          <td>
            <span class="badge bg-info"><?= count($item['sections'] ?? []) ?></span>
          </td>
          <td>
            <span class="badge bg-warning"><?= count($item['images'] ?? []) ?></span>
          </td>
          <td>
            <a href="?page=admin&section=about&action=edit&item_id=<?= urlencode($itemIdStr) ?>"
              class="btn btn-sm btn-warning" title="Modifier">
              <i class="bi bi-pencil"></i>
            </a>
            <a href="?page=admin&section=about&action=delete&item_id=<?= urlencode($itemIdStr) ?>&token=<?= urlencode(\App\Core\Csrf::getToken()) ?>"
              class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr ?')">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <p class="text-muted">Aucun article À propos pour le moment. Créez-en un en utilisant le formulaire ci-dessus.</p>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let sectionIndex = document.querySelectorAll('.section-item').length;
  let imageIndex = document.querySelectorAll('.image-item').length;

  // Ajouter une section
  document.getElementById('addSectionBtn').addEventListener('click', function() {
    const container = document.getElementById('sectionsContainer');
    const html = `
            <div class="section-item card mb-3 p-3" data-index="${sectionIndex}">
                <button type="button" class="btn btn-sm btn-danger float-end remove-section">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
                <div class="mb-3">
                    <label class="form-label">Sous-titre</label>
                    <input type="text" class="form-control section-subtitle" placeholder="ex: Notre Mission">
                </div>
                <div class="mb-3">
                    <label class="form-label">Texte</label>
                    <textarea class="form-control section-text" rows="3" placeholder="Contenu de cette section"></textarea>
                </div>
            </div>
        `;
    container.insertAdjacentHTML('beforeend', html);
    sectionIndex++;
    attachRemoveSectionListener();
  });

  // Ajouter une image
  document.getElementById('addImageBtn').addEventListener('click', function() {
    const container = document.getElementById('imagesContainer');
    const html = `
            <div class="image-item card mb-3 p-3" data-index="${imageIndex}">
                <button type="button" class="btn btn-sm btn-danger float-end remove-image">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
                <div class="mb-3">
                    <label class="form-label">Image</label>
                    <div class="input-group">
                        <input type="hidden" class="image-url" value="">
                        <input type="file" class="form-control image-file" name="aboutImage[]" accept="image/*">
                    </div>
                </div>
            </div>
        `;
    container.insertAdjacentHTML('beforeend', html);
    imageIndex++;
    attachRemoveImageListener();
  });

  function attachRemoveSectionListener() {
    document.querySelectorAll('.remove-section').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        this.closest('.section-item').remove();
      });
    });
  }

  function attachRemoveImageListener() {
    document.querySelectorAll('.remove-image').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        this.closest('.image-item').remove();
      });
    });
  }

  // Préparer les données pour l'envoi
  document.querySelector('form').addEventListener('submit', function(e) {
    // Créer des inputs cachés pour les sections
    const sectionsContainer = document.getElementById('sectionsContainer');
    sectionsContainer.querySelectorAll('.section-item').forEach((item, idx) => {
      const subtitle = item.querySelector('.section-subtitle').value;
      const text = item.querySelector('.section-text').value;

      const subtitleInput = document.createElement('input');
      subtitleInput.type = 'hidden';
      subtitleInput.name = `sections[${idx}][subtitle]`;
      subtitleInput.value = subtitle;
      this.appendChild(subtitleInput);

      const textInput = document.createElement('input');
      textInput.type = 'hidden';
      textInput.name = `sections[${idx}][text]`;
      textInput.value = text;
      this.appendChild(textInput);
    });

    // Créer des inputs cachés pour les images existantes
    const imagesContainer = document.getElementById('imagesContainer');
    imagesContainer.querySelectorAll('.image-item').forEach((item, idx) => {
      const urlInput = item.querySelector('.image-url');
      const fileInput = item.querySelector('.image-file');

      // Si une URL existe déjà et qu'aucun fichier n'a été sélectionné
      if (urlInput.value && !fileInput.files.length) {
        const existingInput = document.createElement('input');
        existingInput.type = 'hidden';
        existingInput.name = `existing_images[${idx}]`;
        existingInput.value = urlInput.value;
        this.appendChild(existingInput);
      }
    });
  });

  attachRemoveSectionListener();
  attachRemoveImageListener();
});
</script>

<style>
.section-item,
.image-item {
  border-left: 4px solid var(--primary-color);
}

.section-item:hover,
.image-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>