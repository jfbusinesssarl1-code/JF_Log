<?php
require_once __DIR__ . '/../_layout_head.php';
include __DIR__ . '/../navbar.php';
?>

<div class="container mt-5">
    <div class="payroll-header">
        <div class="d-flex align-items-center">
            <i class="fas fa-users fa-2x me-3"></i>
            <div>
                <h1 class="mb-1">Gestion des Ouvriers</h1>
                <p class="mb-0 opacity-75"><?= htmlspecialchars($site['name']) ?></p>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_warning'])): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['flash_warning']) ?>
            <?php unset($_SESSION['flash_warning']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-12">
            <a href="?page=payroll&action=siteDetail&id=<?= (string) $site['_id'] ?>" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="?page=payroll&action=createWorker&site_id=<?= (string) $site['_id'] ?>" class="btn btn-primary me-2">
                <i class="fas fa-plus"></i> Ajouter un Ouvrier
            </a>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> Importer depuis Excel
            </button>
        </div>
    </div>

    <!-- Modal d'import -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importer les ouvriers depuis Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="?page=payroll&action=importWorkers&site_id=<?= (string) $site['_id'] ?>" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
                        
                        <div class="alert alert-info">
                            <strong>Format attendu :</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Colonne A :</strong> Noms des ouvriers</li>
                                <li><strong>Colonne B :</strong> Catégorie (T.T ou M.C)</li>
                                <li><strong>Première ligne :</strong> Headers (ignorée)</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="excel_file" class="form-label">Fichier Excel (.xls ou .xlsx)</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" 
                                   accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                   required>
                            <small class="text-muted">Téléchargez votre fichier avec les 2 colonnes</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Importer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (empty($workers)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Aucun ouvrier enregistré pour ce chantier.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Liste des Ouvriers (<?= count($workers) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover payroll-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">
                                    <i class="fas fa-hashtag"></i>
                                </th>
                                <th>
                                    <i class="fas fa-user me-1"></i>Nom
                                </th>
                                <th class="text-center" style="width: 120px;">
                                    <i class="fas fa-tag me-1"></i>Catégorie
                                </th>
                                <th class="text-center" style="width: 100px;">
                                    <i class="fas fa-circle me-1"></i>Statut
                                </th>
                                <th class="text-center" style="width: 150px;">
                                    <i class="fas fa-cogs me-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $index = 1;
                            foreach ($workers as $worker):
                            ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $index++ ?></td>
                                    <td>
                                        <strong class="text-primary"><?= htmlspecialchars($worker['name']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge fs-6 px-3 py-2 <?= ($worker['category'] === 'T.T') ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= htmlspecialchars($worker['category']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge fs-6 px-3 py-2 <?= ($worker['status'] === 'active') ? 'bg-success' : 'bg-secondary' ?>">
                                            <i class="fas fa-<?= ($worker['status'] === 'active') ? 'check' : 'pause' ?> me-1"></i>
                                            <?= ucfirst($worker['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="?page=payroll&action=editWorker&id=<?= (string) $worker['_id'] ?>&site_id=<?= (string) $site['_id'] ?>" 
                                               class="btn btn-sm btn-outline-warning me-1">
                                                <i class="fas fa-edit me-1"></i>Éditer
                                            </a>
                                            <a href="?page=payroll&action=deleteWorker&id=<?= (string) $worker['_id'] ?>&site_id=<?= (string) $site['_id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet ouvrier ?')">
                                                <i class="fas fa-trash me-1"></i>Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
