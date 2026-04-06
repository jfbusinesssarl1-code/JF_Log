<?php
use App\Helpers\AssetHelper;
?>

<style>
.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
  margin-top: 30px;
}

.service-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  position: relative;
}

.service-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.service-icon-container {
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: auto;
}

.service-icon-container img {
  max-width: 100%;
  max-height: 100px;
  object-fit: contain;
}

.service-icon-placeholder {
  font-size: 64px;
  color: white;
}

.service-content {
  padding: 24px;
}

.service-name {
  font-size: 20px;
  font-weight: 700;
  color: #333;
  margin-bottom: 12px;
}

.service-description {
  font-size: 14px;
  color: #666;
  line-height: 1.6;
  margin-bottom: 16px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.service-actions {
  display: flex;
  gap: 6px;
}

.service-actions .btn {
  flex: 1;
  padding: 0.4rem 0.5rem;
  font-size: 0.875rem;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.service-actions .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.service-actions .btn-warning {
  background-color: #ffc107;
  color: #333;
  border: none;
}

.service-actions .btn-warning:hover {
  background-color: #ffb300;
  color: #333;
}

.service-actions .btn-info {
  background-color: #17a2b8;
  color: white;
  border: none;
}

.service-actions .btn-info:hover {
  background-color: #138496;
  color: white;
}

.service-actions .btn-danger {
  background-color: #dc3545;
  color: white;
  border: none;
}

.service-actions .btn-danger:hover {
  background-color: #c82333;
  color: white;
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

/* SEARCH STYLES */
.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 12px;
  color: #999;
  font-size: 16px;
  pointer-events: none;
}

.search-input {
  padding-left: 40px !important;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.search-input:focus {
  border-color: #1a5490;
  box-shadow: 0 0 0 3px rgba(26, 84, 144, 0.1);
}

/* FORM STYLES */
.service-form {
  background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%);
  border: 1px solid #e4ecf4;
  border-radius: 16px;
  padding: 24px;
}

.form-section-title {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #1a5490;
  margin-bottom: 12px;
}

.form-required-note {
  font-size: 12px;
  color: #6c757d;
}

.input-with-icon {
  position: relative;
}

.input-with-icon i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #7a8aa0;
  pointer-events: none;
}

.input-with-icon .form-control {
  padding-left: 40px;
}

.input-with-icon.textarea i {
  top: 12px;
  transform: none;
}

.upload-box {
  border: 2px dashed #d7e2ef;
  border-radius: 12px;
  padding: 14px;
  background: #ffffff;
}

.upload-hint {
  font-size: 12px;
  color: #6c757d;
  margin-top: 6px;
}

.form-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
}
</style>

<div class="section-header">
  <h1><i class="bi bi-briefcase-fill"></i> Gestion des Services</h1>
  <p>Créez et gérez les services offerts par votre organisation</p>
</div>

<div class="modern-card">
  <div class="modern-card-header">
    <i class="bi bi-<?php echo !empty($editServiceId) ? 'pencil-square' : 'plus-circle-fill'; ?>"></i>
    <h5><?php echo !empty($editServiceId) ? 'Modifier le service' : 'Créer un nouveau service'; ?></h5>
  </div>

  <form method="post" enctype="multipart/form-data" action="?page=admin&section=services">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
    <?php if (!empty($editServiceId)): ?>
    <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($editServiceId); ?>">
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="form-section-title mb-0">Formulaire du service</span>
      <span class="form-required-note">* Champ obligatoire</span>
    </div>

    <div class="service-form">
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="form-section-title">Informations principales</div>

          <label for="serviceName" class="form-label fw-semibold">
            Nom du service <span class="text-danger">*</span>
          </label>
          <div class="input-with-icon mb-3">
            <i class="bi bi-tag"></i>
            <input type="text" class="form-control" id="serviceName" name="serviceName"
              placeholder="Ex: Conseil juridique"
              value="<?php echo !empty($editService) ? htmlspecialchars($editService['name'] ?? '') : ''; ?>" required>
          </div>

          <label for="serviceDescription" class="form-label fw-semibold">
            Description
          </label>
          <div class="input-with-icon textarea mb-0">
            <i class="bi bi-text-paragraph"></i>
            <textarea class="form-control" id="serviceDescription" name="serviceDescription" rows="5"
              placeholder="Décrivez ce service en détail..."><?php echo !empty($editService) ? htmlspecialchars($editService['description'] ?? '') : ''; ?></textarea>
          </div>
          <div class="upload-hint">Ajoutez une description claire pour aider les visiteurs.</div>
        </div>

        <div class="col-lg-5">
          <div class="form-section-title">Visuel</div>
          <div class="upload-box">
            <label for="serviceIcon" class="form-label fw-semibold mb-2">
              Icône / Image
            </label>
            <input type="file" class="form-control" id="serviceIcon" name="serviceIcon" accept="image/*">
            <div class="upload-hint">Formats: PNG, JPG, SVG. Taille max: 2 Mo.</div>
            <?php if (!empty($editService['icon'])): ?>
            <div class="mt-3">
              <small class="text-muted">Icône actuelle:</small><br>
              <img src="<?= AssetHelper::url($editService['icon']) ?>"
                style="max-width: 80px; max-height: 80px; margin-top: 8px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="form-actions mt-4">
      <button type="submit" class="btn btn-primary btn-lg px-4">
        <i class="bi bi-<?php echo !empty($editServiceId) ? 'check-circle' : 'plus-circle'; ?>"></i>
        <?php echo !empty($editServiceId) ? 'Enregistrer' : 'Créer'; ?>
      </button>
      <?php if (!empty($editServiceId)): ?>
      <a href="?page=admin&section=services" class="btn btn-secondary btn-lg px-4">
        <i class="bi bi-x-circle"></i> Annuler
      </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="modern-card">
  <div class="modern-card-header">
    <i class="bi bi-grid-3x3-gap-fill"></i>
    <h5>Services existants (<?php echo count($services ?? []); ?>)</h5>
  </div>

  <!-- Barre de recherche et filtres -->
  <div class="activities-toolbar mb-4" id="servicesToolbar">
    <div class="row g-3 align-items-end">
      <div class="col-md-12">
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input type="text" class="form-control search-input" id="serviceSearch"
            placeholder="Chercher un service par nom ou description...">
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($services)): ?>
  <div class="services-grid" id="servicesGrid">
    <?php foreach ($services as $service): ?>
    <div class="service-card" data-name="<?= strtolower($service['name'] ?? '') ?>"
      data-description="<?= strtolower($service['description'] ?? '') ?>"
      data-id="<?= htmlspecialchars((string)$service['_id'] ?? '') ?>">
      <div class="service-icon-container">
        <?php if (!empty($service['icon'])): ?>
        <img src="<?= AssetHelper::url($service['icon']) ?>"
          alt="<?php echo htmlspecialchars($service['name'] ?? ''); ?>">
        <?php else: ?>
        <i class="bi bi-gear-fill service-icon-placeholder"></i>
        <?php endif; ?>
      </div>

      <div class="service-content">
        <h3 class="service-name"><?php echo htmlspecialchars($service['name'] ?? 'Sans nom'); ?></h3>
        <p class="service-description">
          <?php echo htmlspecialchars($service['description'] ?? 'Aucune description'); ?>
        </p>

        <div class="service-actions">
          <a href="?page=admin&section=services&action=edit&service_id=<?php echo htmlspecialchars((string)$service['_id']); ?>"
            class="btn btn-warning btn-sm flex-fill">
            <i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Éditer</span>
          </a>

          <button type="button" class="btn btn-info btn-sm flex-fill"
            onclick="showServiceDetails('<?= htmlspecialchars((string)$service['_id'] ?? '') ?>')"
            title="Voir plus de détails">
            <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Voir plus</span>
          </button>

          <?php $token = \App\Core\Csrf::getToken(); ?>
          <a href="?page=admin&section=services&action=delete&service_id=<?php echo htmlspecialchars((string)$service['_id']); ?>&token=<?php echo urlencode($token); ?>"
            class="btn btn-danger btn-sm flex-fill"
            onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce service ?');">
            <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Supprimer</span>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="text-center py-5">
    <i class="bi bi-briefcase" style="font-size: 80px; color: #ddd;"></i>
    <p class="text-muted mt-3">Aucun service pour le moment</p>
    <p class="text-muted" style="font-size: 14px;">Commencez par créer votre premier service ci-dessus</p>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Détails Service -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" aria-labelledby="serviceDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title" id="serviceDetailsLabel">
          <i class="bi bi-briefcase"></i> Détails du service
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" id="serviceDetailsContent">
        <!-- Content will be loaded here -->
      </div>

      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script>
// Function to show service details in modal
function showServiceDetails(serviceId) {
  const card = document.querySelector(`[data-id="${serviceId}"]`);
  if (!card) {
    alert('Service non trouvé');
    return;
  }

  const name = card.querySelector('.service-name').textContent;
  const description = card.querySelector('.service-description').textContent;
  const image = card.querySelector('.service-icon-container img')?.src || null;

  // Build the modal content
  let content = `
    <div class="row">
      ${image ? `
      <div class="col-md-4 mb-3 mb-md-0 text-center">
        <img src="${image}" class="img-fluid rounded" alt="Service" style="max-height: 150px;">
      </div>
      <div class="col-md-8">
      ` : `
      <div class="col-12">
      `}
        <div class="mb-4">
          <h6 class="text-muted small mb-2">NOM DU SERVICE</h6>
          <h5 class="mb-0">${name}</h5>
        </div>

        <div>
          <h6 class="text-muted small mb-2">DESCRIPTION</h6>
          <p class="mb-0">${description}</p>
        </div>
      </div>
    </div>

    <div class="mt-4 pt-3 border-top">
      <p class="text-muted small mb-0">
        <i class="bi bi-info-circle"></i> ID: <code>${serviceId}</code>
      </p>
    </div>
  `;

  // Update modal content
  document.getElementById('serviceDetailsContent').innerHTML = content;
  document.getElementById('serviceDetailsModal').querySelector('.modal-title').textContent =
    `${name} - Détails du service`;

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('serviceDetailsModal'));
  modal.show();
}
// Service search functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('serviceSearch');
  const servicesGrid = document.getElementById('servicesGrid');

  if (!searchInput || !servicesGrid) return;

  const serviceCards = Array.from(document.querySelectorAll('.service-card'));

  function filterServices() {
    const searchTerm = (searchInput.value || '').toLowerCase();

    let filtered = serviceCards.filter(card => {
      const name = (card.dataset.name || '').toLowerCase();
      const description = (card.dataset.description || '').toLowerCase();

      return !searchTerm || name.includes(searchTerm) || description.includes(searchTerm);
    });

    servicesGrid.innerHTML = '';

    if (filtered.length === 0) {
      const emptyState = document.createElement('div');
      emptyState.className = 'text-center py-5';
      emptyState.style.gridColumn = '1 / -1';
      emptyState.innerHTML = `
        <i class="bi bi-search" style="font-size: 48px; color: #ddd; display: block;"></i>
        <p class="text-muted mt-3">Aucun service trouvé</p>
        <p class="text-muted" style="font-size: 14px;">Essayez de modifier votre recherche</p>
      `;
      servicesGrid.appendChild(emptyState);
    } else {
      filtered.forEach(card => {
        servicesGrid.appendChild(card.cloneNode(true));
      });
    }
  }

  searchInput.addEventListener('input', filterServices);
});
</script>