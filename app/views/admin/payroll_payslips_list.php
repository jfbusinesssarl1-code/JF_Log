<?php
require_once __DIR__ . '/../_layout_head.php';
include __DIR__ . '/../navbar.php';
?>

<div class="container-fluid mt-5">
    <h1>Fiches de Paie - <?= htmlspecialchars($site['name']) ?></h1>

    <div class="row mb-3">
        <div class="col-md-12">
            <a href="?page=payroll&action=siteDetail&id=<?= (string) $site['_id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="?page=payroll&action=attendance&site_id=<?= (string) $site['_id'] ?>" class="btn btn-primary">
                <i class="fas fa-list-check"></i> Saisir Présences
            </a>
        </div>
    </div>

    <?php if (empty($payslips)): ?>
        <div class="alert alert-info">Aucune fiche de paie générée pour ce chantier.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Période</th>
                        <th class="text-right">Total Salaires</th>
                        <th class="text-center">Ouvriers</th>
                        <th class="text-center">Date Création</th>
                        <th class="text-center">Statut</th>
                        <th width="250">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payslips as $payslip): ?>
                        <tr<?= (isset($payslip['archived']) && $payslip['archived']) ? ' style="opacity: 0.6; background-color: #f0f0f0;"' : '' ?>>
                            <td>
                                <strong>
                                    <?= htmlspecialchars($payslip['week_start'] ?? $payslip['week_of'] ?? '-') ?>
                                    <span class="text-muted">→</span>
                                    <?= htmlspecialchars($payslip['week_end'] ?? $payslip['week_of'] ?? '-') ?>
                                </strong>
                            </td>
                            <td class="text-right">
                                <strong>$<?= number_format($payslip['total_salary'] ?? 0, 2) ?></strong>
                            </td>
                            <td class="text-center">
                                <?= count($payslip['payroll'] ?? []) ?>
                            </td>
                            <td class="text-center">
                                <small><?= htmlspecialchars($payslip['created_at'] ?? '-') ?></small>
                            </td>
                            <td class="text-center">
                                <?php if (isset($payslip['archived']) && $payslip['archived']): ?>
                                    <span class="badge bg-secondary">Archivée</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?page=payroll&action=payslip&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($payslip['week_of']) ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Consulter
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
