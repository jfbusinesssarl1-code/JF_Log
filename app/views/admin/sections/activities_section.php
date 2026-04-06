<?php
use App\Models\ActivityModel;
use App\Helpers\AssetHelper;

$activityModel = new ActivityModel();
$activities = $activityModel->getAll();

// Compter les activités par statut
$statusCounts = [
    'En cours' => 0,
    'Planifié' => 0,
    'En attente' => 0
];
foreach ($activities as $a) {
    $status = $a['status'] ?? 'En attente';
    if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
    }
}
?>

<style>
.activities-section {
  --primary: #1a5490;
  --success: #28a745;
  --warning: #ffc107;
  --info: #17a2b8;
  --danger: #dc3545;
}

/* STATS CARDS */
.activity-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--primary), #2a7bc0);
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  background: linear-gradient(135deg, var(--primary), #2a7bc0);
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
  color: var(--primary);
  line-height: 1;
}

.stat-label {
  font-size: 13px;
  color: #666;
  margin-top: 8px;
  font-weight: 500;
}

/* ACTIVITY GRID */
.activity-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
  margin-bottom: 30px;
}

.activity-card {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid #f0f0f0;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.activity-card:hover {
  transform: translateY(-12px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border-color: var(--primary);
}

.activity-image-wrapper {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.activity-image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.activity-card:hover .activity-image-wrapper img {
  transform: scale(1.05);
}

.activity-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 56px;
  opacity: 0.8;
}

.activity-status-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  gap: 4px;
}

.activity-status-badge.en-cours {
  background: rgba(40, 167, 69, 0.92);
  color: white;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.activity-status-badge.planifie {
  background: rgba(23, 162, 184, 0.92);
  color: white;
  box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
}

.activity-status-badge.en-attente {
  background: rgba(255, 193, 7, 0.92);
  color: #333;
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.activity-content {
  padding: 20px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.activity-title {
  font-size: 17px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 10px;
  line-height: 1.4;
  letter-spacing: -0.3px;
}

.activity-description {
  font-size: 13px;
  color: #555;
  line-height: 1.5;
  margin-bottom: 14px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex-grow: 1;
}

.activity-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
  margin-bottom: 14px;
  font-size: 12px;
  color: #888;
}

.activity-meta i {
  color: var(--primary);
  font-size: 14px;
}

.activity-actions {
  display: flex;
  gap: 6px;
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

.btn-activity {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 10px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 600;
  border: none;
  transition: all 0.2s ease;
  cursor: pointer;
  text-decoration: none;
  background-color: transparent;
  color: inherit;
  text-align: center;
}

.btn-activity-edit {
  background: #fff3cd;
  color: #856404;
}

.btn-activity-edit:hover {
  background: #ffc107;
  color: #fff;
  transform: translateY(-2px);
}

.btn-activity-delete {
  background: #f8d7da;
  color: #721c24;
}

.btn-activity-delete:hover {
  background: #dc3545;
  color: white;
  transform: translateY(-2px);
}

.btn-activity-view {
  background: #d1ecf1;
  color: #0c5460;
}

.btn-activity-view:hover {
  background: #17a2b8;
  color: white;
  transform: translateY(-2px);
  text-decoration: none;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-radius: 14px;
  color: #555;
}

.empty-state i {
  font-size: 64px;
  color: #bbb;
  margin-bottom: 20px;
  opacity: 0.6;
}

.form-card {
  background: white;
  border-radius: 14px;
  padding: 32px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin-bottom: 40px;
  border: 1px solid #f0f0f0;
}

.form-card-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid #f0f0f0;
}

.form-card-header i {
  font-size: 32px;
  color: var(--primary);
}

.form-card-header h5 {
  margin: 0;
  font-weight: 700;
  color: #1a1a1a;
  font-size: 20px;
}

.form-control:focus,
.form-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 0.25rem rgba(26, 84, 144, 0.15);
}

.preview-image {
  max-width: 100%;
  max-height: 120px;
  border-radius: 8px;
  margin-top: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.activities-header {
  margin-bottom: 28px;
  padding-bottom: 16px;
  border-bottom: 3px dashed #e0e0e0;
}

.activities-header h4 {
  margin: 0;
  font-weight: 700;
  color: var(--primary);
  font-size: 22px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.activities-header p {
  margin: 8px 0 0 0;
  color: #666;
  font-size: 14px;
}

/* TOOLBAR STYLES */
.activities-toolbar {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border: 1px solid #f0f0f0;
}

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
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(26, 84, 144, 0.1);
}

.filter-select,
.sort-select {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.filter-select:focus,
.sort-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(26, 84, 144, 0.1);
}

/* ACTIVITY VIEW BUTTON */
.btn-activity-view {
  background: linear-gradient(135deg, var(--primary), #2a7bc0);
  color: white;
}

.btn-activity-view:hover {
  background: linear-gradient(135deg, #1a5490, #1a5490);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 84, 144, 0.3);
  color: white;
  text-decoration: none;
}

/* ANIMATION */
.activity-card {
  animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .activity-grid {
    grid-template-columns: 1fr;
  }

  .activity-actions {
    gap: 4px;
  }

  .btn-activity span {
    display: none;
  }

  .activities-toolbar .col-md-3 {
    margin-top: 10px;
  }
}

.activity-info-divider {
  height: 0px;
}
</style>

<div class="activities-section">
  <div class="section-header">
    <h1><i class="bi bi-clock-history"></i> Gestion des Activités</h1>
    <p>Créez et gérez les projets et activités en cours de votre organisation</p>
  </div>

  <!-- Statistiques -->
  <div class="activity-stats">
    <div class="stat-card primary">
      <div class="stat-card-header">
        <div class="stat-icon primary">
          <i class="bi bi-collection"></i>
        </div>
      </div>
      <div class="stat-value"><?php echo count($activities); ?></div>
      <div class="stat-label">Total Activités</div>
    </div>

    <div class="stat-card success">
      <div class="stat-card-header">
        <div class="stat-icon success">
          <i class="bi bi-play-circle-fill"></i>
        </div>
      </div>
      <div class="stat-value"><?php echo $statusCounts['En cours']; ?></div>
      <div class="stat-label">En cours</div>
    </div>

    <div class="stat-card info">
      <div class="stat-card-header">
        <div class="stat-icon info">
          <i class="bi bi-calendar-check"></i>
        </div>
      </div>
      <div class="stat-value"><?php echo $statusCounts['Planifié']; ?></div>
      <div class="stat-label">Planifiées</div>
    </div>

    <div class="stat-card warning">
      <div class="stat-card-header">
        <div class="stat-icon warning">
          <i class="bi bi-hourglass-split"></i>
        </div>
      </div>
      <div class="stat-value"><?php echo $statusCounts['En attente']; ?></div>
      <div class="stat-label">En attente</div>
    </div>
  </div>

  <!-- Formulaire d'ajout/édition -->
  <div class="form-card">
    <div class="form-card-header">
      <i class="bi <?php echo !empty($editActivityId) ? 'bi-pencil-square' : 'bi-plus-circle-fill'; ?>"></i>
      <h5><?php echo !empty($editActivityId) ? 'Modifier l\'activité' : 'Créer une nouvelle activité'; ?></h5>
    </div>

    <form method="post" enctype="multipart/form-data" action="?page=admin&section=activities">
      <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
      <?php if (!empty($editActivityId)): ?>
      <input type="hidden" name="activity_id" value="<?= htmlspecialchars($editActivityId) ?>">
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label for="activityTitle" class="form-label fw-semibold">
            <i class="bi bi-pencil"></i> Titre du projet <span class="text-danger">*</span>
          </label>
          <input type="text" class="form-control" id="activityTitle" name="activityTitle"
            placeholder="Ex: Construction d'une école à Kinshasa"
            value="<?php echo !empty($editActivity) ? htmlspecialchars($editActivity['title'] ?? '') : ''; ?>" required>
        </div>

        <div class="col-md-3">
          <label for="activityStatus" class="form-label fw-semibold">
            <i class="bi bi-flag"></i> Statut <span class="text-danger">*</span>
          </label>
          <select class="form-select" id="activityStatus" name="activityStatus" required>
            <?php 
                        $statuses = ['En cours', 'Planifié', 'En attente'];
                        $sel = !empty($editActivity['status']) ? $editActivity['status'] : 'En cours'; 
                        ?>
            <?php foreach ($statuses as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>" <?= ($s === $sel) ? 'selected' : '' ?>>
              <?= htmlspecialchars($s) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label for="activityDate" class="form-label fw-semibold">
            <i class="bi bi-calendar3"></i> Date de début
          </label>
          <input type="date" class="form-control" id="activityDate" name="activityDate"
            value="<?php echo !empty($editActivity) ? htmlspecialchars($editActivity['date'] ?? '') : ''; ?>">
        </div>
      </div>

      <div class="mb-3">
        <label for="activityDescription" class="form-label fw-semibold">
          <i class="bi bi-text-paragraph"></i> Description
        </label>
        <textarea class="form-control" id="activityDescription" name="activityDescription" rows="4"
          placeholder="Décrivez le projet, ses objectifs et son avancement..."><?php echo !empty($editActivity) ? htmlspecialchars($editActivity['description'] ?? '') : ''; ?></textarea>
      </div>

      <div class="mb-4">
        <label for="activityImage" class="form-label fw-semibold">
          <i class="bi bi-image"></i> Image du projet
        </label>
        <input type="file" class="form-control" id="activityImage" name="activityImage" accept="image/*"
          onchange="previewActivityImage(this)">
        <small class="text-muted">Format recommandé: JPG, PNG, WebP - Max 5 Mo</small>

        <?php if (!empty($editActivity['image'])): ?>
        <div class="mt-3">
          <p class="mb-2 text-muted small">Image actuelle:</p>
          <img src="<?= AssetHelper::url($editActivity['image']) ?>" class="preview-image" alt="Aperçu">
        </div>
        <?php endif; ?>

        <div id="imagePreview" class="mt-3" style="display: none;">
          <p class="mb-2 text-muted small">Nouvel aperçu:</p>
          <img id="previewImg" class="preview-image" alt="Aperçu">
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-lg px-4">
          <i class="bi <?php echo !empty($editActivityId) ? 'bi-check-circle' : 'bi-plus-circle'; ?>"></i>
          <?php echo !empty($editActivityId) ? 'Enregistrer les modifications' : 'Créer l\'activité'; ?>
        </button>
        <?php if (!empty($editActivityId)): ?>
        <a href="?page=admin&section=activities" class="btn btn-secondary btn-lg px-4">
          <i class="bi bi-x-circle"></i> Annuler
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- ACTIVITÉS EXISTANTES -->
  <div class="activities-header">
    <h4><i class="bi bi-list-ul"></i> Activités Existantes</h4>
    <p><?php echo count($activities); ?> activités trouvées dans le système</p>
  </div>

  <!-- Barre de recherche et filtres -->
  <div class="activities-toolbar mb-4" id="activitiesToolbar">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input type="text" class="form-control search-input" id="activitySearch"
            placeholder="Chercher une activité par titre ou description...">
        </div>
      </div>

      <div class="col-md-3">
        <label class="form-label small fw-semibold">Filtrer par statut</label>
        <select class="form-select form-select-sm filter-select" id="statusFilter">
          <option value="">Tous les statuts</option>
          <option value="En cours">En cours</option>
          <option value="Planifié">Planifiées</option>
          <option value="En attente">En attente</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label small fw-semibold">Trier par</label>
        <select class="form-select form-select-sm sort-select" id="sortFilter">
          <option value="recent">Plus récentes</option>
          <option value="ancien">Plus anciennes</option>
          <option value="title">Titre (A-Z)</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Grille d'activités -->
  <div class="activity-grid" id="activityGrid">
    <?php if (empty($activities)): ?>
    <div class="empty-state" style="grid-column: 1 / -1;">
      <i class="bi bi-inbox"></i>
      <h5 class="mt-3 mb-2">Aucune activité trouvée</h5>
      <p class="mb-0">Créez votre première activité en utilisant le formulaire ci-dessus</p>
    </div>
    <?php else: ?>
    <?php foreach ($activities as $activity): ?>
    <div class="activity-card" data-title="<?= strtolower($activity['title'] ?? '') ?>"
      data-description="<?= strtolower($activity['description'] ?? '') ?>"
      data-status="<?= htmlspecialchars($activity['status'] ?? 'En attente') ?>"
      data-date="<?= htmlspecialchars($activity['date'] ?? '') ?>"
      data-id="<?= htmlspecialchars((string)$activity['_id'] ?? '') ?>">

      <!-- Image Section -->
      <div class="activity-image-wrapper">
        <?php if (!empty($activity['image'])): ?>
        <img src="<?= AssetHelper::url($activity['image']) ?>"
          alt="<?= htmlspecialchars($activity['title'] ?? 'Activité') ?>" loading="lazy">
        <?php else: ?>
        <div class="activity-placeholder">
          <i class="bi bi-briefcase"></i>
        </div>
        <?php endif; ?>

        <!-- Status Badge -->
        <div
          class="activity-status-badge <?= strtolower(str_replace(' ', '-', $activity['status'] ?? 'En attente')) ?>">
          <i class="bi <?php 
              $st = $activity['status'] ?? 'En attente';
              echo ($st === 'En cours') ? 'bi-play-circle-fill' : 
                   (($st === 'Planifié') ? 'bi-calendar-check' : 'bi-hourglass-split'); 
            ?>"></i>
          <?= htmlspecialchars($activity['status'] ?? 'En attente') ?>
        </div>
      </div>

      <!-- Content Section -->
      <div class="activity-content">
        <h5 class="activity-title"><?= htmlspecialchars($activity['title'] ?? 'Sans titre') ?></h5>

        <p class="activity-description">
          <?= htmlspecialchars($activity['description'] ?? 'Aucune description') ?>
        </p>

        <!-- Metadata -->
        <div class="activity-meta">
          <?php if (!empty($activity['date'])): ?>
          <span>
            <i class="bi bi-calendar-event"></i>
            <?= date('d M Y', strtotime($activity['date'])) ?>
          </span>
          <?php endif; ?>

          <?php 
              $status = $activity['status'] ?? 'En attente';
              $progressClass = ($status === 'En cours') ? 'text-success' : 
                             (($status === 'Planifié') ? 'text-info' : 'text-warning');
            ?>
          <span class="<?= $progressClass ?>">
            <i class="bi bi-info-circle"></i>
            Progression
          </span>
        </div>

        <!-- Quick Info -->
        <div class="activity-info-divider"></div>
      </div>

      <!-- Actions Section -->
      <div class="activity-actions">
        <a href="?page=admin&section=activities&action=edit&activity_id=<?= htmlspecialchars((string)$activity['_id'] ?? '') ?>"
          class="btn-activity btn-activity-edit" title="Modifier l'activité">
          <i class="bi bi-pencil"></i>
          <span class="d-none d-sm-inline">Éditer</span>
        </a>

        <button type="button" class="btn-activity btn-activity-view"
          onclick="showActivityDetails('<?= htmlspecialchars((string)$activity['_id'] ?? '') ?>')"
          title="Voir plus de détails">
          <i class="bi bi-eye"></i>
          <span class="d-none d-sm-inline">Voir plus</span>
        </button>

        <a href="?page=admin&section=activities&action=delete&activity_id=<?= htmlspecialchars((string)$activity['_id'] ?? '') ?>&token=<?= \App\Core\Csrf::getToken() ?>"
          class="btn-activity btn-activity-delete"
          onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')" title="Supprimer l'activité">
          <i class="bi bi-trash"></i>
          <span class="d-none d-sm-inline">Supprimer</span>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Modal Détails Activité -->
  <div class="modal fade" id="activityDetailsModal" tabindex="-1" aria-labelledby="activityDetailsLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="activityDetailsLabel">
            <i class="bi bi-briefcase"></i> Détails de l'activité
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body" id="activityDetailsContent">
          <!-- Content will be loaded here -->
        </div>

        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function previewActivityImage(input) {
  const preview = document.getElementById('imagePreview');
  const previewImg = document.getElementById('previewImg');

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

// SEARCH AND FILTER FUNCTIONALITY
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('activitySearch');
  const statusFilter = document.getElementById('statusFilter');
  const sortFilter = document.getElementById('sortFilter');
  const activityGrid = document.getElementById('activityGrid');
  const activityCards = Array.from(document.querySelectorAll('.activity-card'));

  function filterAndSort() {
    const searchTerm = (searchInput?.value || '').toLowerCase();
    const selectedStatus = statusFilter?.value || '';
    const sortOption = sortFilter?.value || 'recent';

    let filtered = activityCards.filter(card => {
      const title = (card.dataset.title || '').toLowerCase();
      const description = (card.dataset.description || '').toLowerCase();
      const status = card.dataset.status || '';

      const matchesSearch = !searchTerm || title.includes(searchTerm) || description.includes(searchTerm);
      const matchesStatus = !selectedStatus || status === selectedStatus;

      return matchesSearch && matchesStatus;
    });

    // Apply sorting
    filtered.sort((a, b) => {
      switch (sortOption) {
        case 'recent':
          return new Date(b.dataset.date) - new Date(a.dataset.date);
        case 'ancien':
          return new Date(a.dataset.date) - new Date(b.dataset.date);
        case 'title':
          return (a.dataset.title || '').localeCompare(b.dataset.title || '');
        default:
          return 0;
      }
    });

    // Clear and re-render
    activityGrid.innerHTML = '';

    if (filtered.length === 0) {
      const emptyState = document.createElement('div');
      emptyState.className = 'empty-state';
      emptyState.style.gridColumn = '1 / -1';
      emptyState.innerHTML = `
        <i class="bi bi-search"></i>
        <h5 class="mt-3 mb-2">Aucune activité trouvée</h5>
        <p class="mb-0">Essayez de modifier vos critères de recherche ou filtres</p>
      `;
      activityGrid.appendChild(emptyState);
    } else {
      filtered.forEach(card => {
        activityGrid.appendChild(card.cloneNode(true));
      });
    }
  }

  // Add event listeners
  searchInput?.addEventListener('input', filterAndSort);
  statusFilter?.addEventListener('change', filterAndSort);
  sortFilter?.addEventListener('change', filterAndSort);

  // Highlight search terms
  searchInput?.addEventListener('input', function() {
    // Optional: Add highlighting feature here
  });
});

// Function to show activity details in modal
function showActivityDetails(activityId) {
  const card = document.querySelector(`[data-id="${activityId}"]`);
  if (!card) {
    alert('Activité non trouvée');
    return;
  }

  const title = card.querySelector('.activity-title').textContent;
  const description = card.querySelector('.activity-description').textContent;
  const status = card.dataset.status;
  const date = card.dataset.date;
  const image = card.querySelector('.activity-image-wrapper img')?.src || null;

  // Format the date
  let formattedDate = 'Non spécifiée';
  if (date) {
    try {
      const dateObj = new Date(date);
      formattedDate = dateObj.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    } catch (e) {
      formattedDate = date;
    }
  }

  // Determine status badge color
  let statusBadgeClass = 'bg-warning';
  let statusIcon = 'bi-hourglass-split';
  if (status === 'En cours') {
    statusBadgeClass = 'bg-success';
    statusIcon = 'bi-play-circle-fill';
  } else if (status === 'Planifié') {
    statusBadgeClass = 'bg-info';
    statusIcon = 'bi-calendar-check';
  }

  // Build the modal content
  let content = `
    <div class="row">
      ${image ? `
      <div class="col-md-4 mb-3 mb-md-0">
        <img src="${image}" class="img-fluid rounded" alt="Activité">
      </div>
      <div class="col-md-8">
      ` : `
      <div class="col-12">
      `}
        <div class="mb-4">
          <h6 class="text-muted small mb-2">TITRE</h6>
          <h5 class="mb-0">${title}</h5>
        </div>

        <div class="mb-4">
          <h6 class="text-muted small mb-2">STATUT</h6>
          <span class="badge ${statusBadgeClass}">
            <i class="bi ${statusIcon}"></i> ${status}
          </span>
        </div>

        <div class="mb-4">
          <h6 class="text-muted small mb-2">DATE DE DÉBUT</h6>
          <p class="mb-0">
            <i class="bi bi-calendar-event text-primary"></i> ${formattedDate}
          </p>
        </div>

        <div>
          <h6 class="text-muted small mb-2">DESCRIPTION</h6>
          <p class="mb-0">${description}</p>
        </div>
      </div>
    </div>

    <div class="mt-4 pt-3 border-top">
      <p class="text-muted small mb-0">
        <i class="bi bi-info-circle"></i> ID: <code>${activityId}</code>
      </p>
    </div>
  `;

  // Update modal content
  document.getElementById('activityDetailsContent').innerHTML = content;
  document.getElementById('activityDetailsModal').querySelector('.modal-title').textContent =
    `${title} - Détails de l'activité`;

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('activityDetailsModal'));
  modal.show();
}
</script>