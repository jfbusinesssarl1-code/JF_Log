<?php
require_once __DIR__ . '/../_layout_head.php';
?>

<div class="container mt-5">
    <h1><?= htmlspecialchars($site['name']) ?></h1>

    <div class="row mb-4">
        <div class="col-md-12">
            <a href="?page=payroll&action=sites" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="?page=payroll&action=editSite&id=<?= (string) $site['_id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Éditer
            </a>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Informations du Chantier</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Localisation:</strong> <?= htmlspecialchars($site['location'] ?? '-') ?></p>
                    <p><strong>Ingénieur (Ir.):</strong> <?= htmlspecialchars($site['engineer_name'] ?? '-') ?></p>
                    <p><strong>Tél Ir.:</strong> <?= htmlspecialchars($site['engineer_phone'] ?? '-') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Magasinier (Mag):</strong> <?= htmlspecialchars($site['warehouse_manager_name'] ?? '-') ?></p>
                    <p><strong>Statut:</strong> 
                        <span class="badge bg-<?= ($site['status'] === 'active') ? 'success' : 'secondary' ?>">
                            <?= ucfirst($site['status']) ?>
                        </span>
                    </p>
                </div>
            </div>
            <p><strong>Description:</strong> <?= htmlspecialchars($site['description'] ?? '-') ?></p>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?= $stats['total_workers'] ?? 0 ?></h3>
                    <p class="text-muted">Ouvriers totaux</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?= $stats['workers_tc'] ?? 0 ?></h3>
                    <p class="text-muted">Tout Travaux (T.T)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?= $stats['workers_mc'] ?? 0 ?></h3>
                    <p class="text-muted">Maçons (M.C)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules de gestion -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5>Modules de Gestion</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <a href="?page=payroll&action=workers&site_id=<?= (string) $site['_id'] ?>" class="btn btn-block btn-outline-primary w-100 mb-2">
                        <i class="fas fa-people-carry"></i> Gestion Ouvriers
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?page=payroll&action=salaryConfig&site_id=<?= (string) $site['_id'] ?>" class="btn btn-block btn-outline-info w-100 mb-2">
                        <i class="fas fa-cogs"></i> Configuration Salaires
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?page=payroll&action=attendance&site_id=<?= (string) $site['_id'] ?>" class="btn btn-block btn-outline-warning w-100 mb-2">
                        <i class="fas fa-list-check"></i> Saisir Présences
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="?page=payroll&action=payslips&site_id=<?= (string) $site['_id'] ?>" class="btn btn-block btn-outline-success w-100 mb-2">
                        <i class="fas fa-file-invoice-dollar"></i> Fiches de Paie
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
