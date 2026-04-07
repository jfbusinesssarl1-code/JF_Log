<?php
require_once __DIR__ . '/../_layout_head.php';
include __DIR__ . '/../navbar.php';
?>

<div class="container mt-5">
    <h1>Gestion de la Paie - Chantiers</h1>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-12">
            <a href="?page=payroll&action=createSite" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Chantier
            </a>
            <a href="?page=payroll&action=weeklyReportSynthesis" class="btn btn-success" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                <i class="fas fa-chart-bar"></i> Rapport Synthèse Hebdomadaire
            </a>
        </div>
    </div>

    <?php if (empty($sites)): ?>
        <div class="alert alert-info">Aucun chantier enregistré.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nom</th>
                        <th>Localisation</th>
                        <th>Ingénieur</th>
                        <th>Tél Ir.</th>
                        <th>Magasinier</th>
                        <th>Statut</th>
                        <th>Ouvriers</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sites as $site): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($site['name']) ?></strong></td>
                            <td><?= htmlspecialchars($site['location'] ?? '') ?></td>
                            <td><?= htmlspecialchars($site['engineer_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($site['engineer_phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($site['warehouse_manager_name'] ?? '') ?></td>
                            <td>
                                <span class="badge bg-<?= ($site['status'] === 'active') ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($site['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $siteModel = new \App\Models\SiteModel();
                                $stats = $siteModel->getStats((string) $site['_id']);
                                ?>
                                <strong><?= $stats['total_workers'] ?></strong>
                                <small class="text-muted">(T.T: <?= $stats['workers_tc'] ?>, M.C: <?= $stats['workers_mc'] ?>)</small>
                            </td>
                            <td>
                                <a href="?page=payroll&action=siteDetail&id=<?= (string) $site['_id'] ?>" 
                                   class="btn btn-sm btn-info me-2">
                                    <i class="fas fa-eye"></i> Détail
                                </a>
                                <a href="?page=payroll&action=editSite&id=<?= (string) $site['_id'] ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Éditer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
