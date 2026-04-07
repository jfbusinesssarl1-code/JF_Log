<?php
require_once __DIR__ . '/../_layout_head.php';
include __DIR__ . '/../navbar.php';

function getDayName($dayKey) {
    $dayNames = [
        'monday' => 'Lundi',
        'tuesday' => 'Mardi',
        'wednesday' => 'Mercredi',
        'thursday' => 'Jeudi',
        'friday' => 'Vendredi',
        'saturday' => 'Samedi'
    ];
    return $dayNames[$dayKey] ?? $dayKey;
}
?>

<div class="container-fluid mt-5">
    <div class="payroll-header">
        <div class="d-flex align-items-center">
            <i class="fas fa-file-invoice-dollar fa-2x me-3"></i>
            <div>
                <h1 class="mb-1">Fiche de Paie</h1>
                <p class="mb-0 opacity-75">
                    <?= htmlspecialchars($site['name']) ?> - Semaine du <?= htmlspecialchars($week_of) ?>
                </p>
            </div>
        </div>
    </div>

    <?php $isArchived = isset($payslip['archived']) && $payslip['archived']; ?>
<div class="row mb-3">
        <div class="col-md-12">
            <a href="?page=payroll&action=payslips&site_id=<?= (string) $site['_id'] ?>" class="btn btn-secondary me-2">
                <i class="fas fa-arrows-alt-h"></i> Liste des Fiches
            </a>
            <?php if ($isArchived): ?>
                <span class="badge bg-secondary me-2">Fiche archivée - lecture seule</span>
                <a href="?page=payroll&action=unarchivePayslip&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-warning me-2"
                   onclick="return confirm('Réactiver cette fiche de paie pour modification ?');">
                    <i class="fas fa-undo"></i> Restaurer
                </a>
                <a href="?page=payroll&action=exportPayslipPDF&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-warning me-2">
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </a>
                <a href="?page=payroll&action=exportPresencePDF&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-info me-2">
                    <i class="fas fa-clipboard-list"></i> Fiche Présence
                </a>
            <?php else: ?>
                <a href="?page=payroll&action=attendance&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-primary me-2">
                    <i class="fas fa-edit"></i> Modifier Présences
                </a>
                <form method="POST" action="?page=payroll&action=savePayslip&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                      style="display: inline; margin-right: 0.5rem;">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </form>
                <a href="?page=payroll&action=exportPayslipPDF&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-warning me-2">
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </a>
                <a href="?page=payroll&action=exportPresencePDF&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-info me-2">
                    <i class="fas fa-clipboard-list"></i> Fiche Présence
                </a>
                <a href="?page=payroll&action=archivePayslip&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-info me-2"
                   onclick="return confirm('Êtes-vous sûr de vouloir archiver cette fiche de paie ?');">
                    <i class="fas fa-archive"></i> Archiver
                </a>
                <a href="?page=payroll&action=deletePayslip&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette fiche de paie ? Cette action est irréversible.');">
                    <i class="fas fa-trash"></i> Supprimer
                </a>
            <?php endif; ?>
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

    <?php if (!isset($payslip) || !$payslip || empty($payslip['payroll'])): ?>
        <div class="alert alert-info">
            <h5>Aucune fiche de paie disponible</h5>
            <p>Il n'y a pas de présence enregistrée pour cette semaine (<?= htmlspecialchars($week_of) ?>).</p>
            <p>Veuillez d'abord <a href="?page=payroll&action=attendance&site_id=<?= (string) $site['_id'] ?>&week_of=<?= htmlspecialchars($week_of) ?>" class="alert-link">saisir les présences</a>.</p>
        </div>
    <?php else: ?>

    <!-- Entête avec infos du site -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Chantier:</strong> <?= htmlspecialchars($site['name']) ?></p>
                    <p><strong>Localisation:</strong> <?= htmlspecialchars($site['location'] ?? '-') ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Ingénieur (Ir.):</strong> <?= htmlspecialchars($site['engineer_name'] ?? '-') ?></p>
                    <p><strong>Magasinier (Mag):</strong> <?= htmlspecialchars($site['warehouse_manager_name'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLEAU 1: Tableau de Paie des Ouvriers -->
    <div class="card mb-4 shadow-sm payroll-card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-calculator me-2"></i>
                Tableau de Paie Hebdomadaire
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover payroll-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">
                                <i class="fas fa-hashtag"></i>
                            </th>
                            <th style="min-width: 150px;">
                                <i class="fas fa-user me-1"></i>Noms
                            </th>
                            <th class="text-center" style="width: 100px;">
                                <i class="fas fa-tag me-1"></i>Catégorie
                            </th>
                            <th class="text-center" style="width: 60px;" title="Lundi">
                                <i class="fas fa-calendar-day me-1"></i>Lun
                            </th>
                            <th class="text-center" style="width: 60px;" title="Mardi">
                                <i class="fas fa-calendar-day me-1"></i>Mar
                            </th>
                            <th class="text-center" style="width: 60px;" title="Mercredi">
                                <i class="fas fa-calendar-day me-1"></i>Mer
                            </th>
                            <th class="text-center" style="width: 60px;" title="Jeudi">
                                <i class="fas fa-calendar-day me-1"></i>Jeu
                            </th>
                            <th class="text-center" style="width: 60px;" title="Vendredi">
                                <i class="fas fa-calendar-day me-1"></i>Ven
                            </th>
                            <th class="text-center" style="width: 60px;" title="Samedi">
                                <i class="fas fa-calendar-day me-1"></i>Sam
                            </th>
                            <th class="text-center" style="width: 80px;" title="Jours Prestés">
                                <i class="fas fa-clock me-1"></i>Jrs
                            </th>
                            <th class="text-right" style="width: 100px;">
                                <i class="fas fa-dollar-sign me-1"></i>Sal Hebdo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($payslip['payroll']) && !empty($payslip['payroll'])):
                            $index = 1;
                            $totalPayroll = 0;
                            foreach ($payslip['payroll'] as $row):
                                $totalPayroll += $row['weekly_salary'];
                        ?>
                            <tr>
                                <td class="text-center"><?= $index++ ?></td>
                                <td><?= htmlspecialchars($row['worker_name']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= ($row['category'] === 'T.T') ? 'success' : 'warning' ?>">
                                        <?= htmlspecialchars($row['category']) ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $row['monday'] > 0 ? ($row['monday'] == 1 ? '✓' : '½') : '-' ?></td>
                                <td class="text-center"><?= $row['tuesday'] > 0 ? ($row['tuesday'] == 1 ? '✓' : '½') : '-' ?></td>
                                <td class="text-center"><?= $row['wednesday'] > 0 ? ($row['wednesday'] == 1 ? '✓' : '½') : '-' ?></td>
                                <td class="text-center"><?= $row['thursday'] > 0 ? ($row['thursday'] == 1 ? '✓' : '½') : '-' ?></td>
                                <td class="text-center"><?= $row['friday'] > 0 ? ($row['friday'] == 1 ? '✓' : '½') : '-' ?></td>
                                <td class="text-center"><?= $row['saturday'] > 0 ? ($row['saturday'] == 1 ? '✓' : '½') : '-' ?></td>
                                <td class="text-center"><strong><?= $row['days_worked'] ?></strong></td>
                                <td class="text-right"><strong>$<?= number_format($row['weekly_salary'], 2) ?></strong></td>
                            </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">Aucun enregistrement de paie</td>
                            </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="10" class="text-right">TOTAL SALAIRES HEBDO:</th>
                            <th class="text-right">
                                <strong>$<?= number_format($payslip['total_salary'] ?? $totalPayroll ?? 0, 2) ?></strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- TABLEAU 2: Synthèse Journalière T.T -->
        <div class="col-md-6">
            <div class="card shadow-sm payroll-card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Synthèse Journalière T.T (Tout Travaux)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover payroll-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 100px;">
                                        <i class="fas fa-calendar-week me-1"></i>Jour
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-users me-1"></i>Volume Journalier
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-equals me-1"></i>Équivalence
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($payslip['daily_synthesis_tc']) && !empty($payslip['daily_synthesis_tc'])):
                                    foreach ($payslip['daily_synthesis_tc'] as $row):
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['day']) ?></td>
                                        <td class="text-center"><?= $row['daily_volume'] ?></td>
                                        <td class="text-center"><strong>$<?= number_format($row['equivalence'], 2) ?></strong></td>
                                    </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Aucune donnée</td>
                                    </tr>
                                <?php
                                endif;
                                ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>TOTAL</td>
                                    <td class="text-center">-</td>
                                    <td class="text-center"><strong>$<?= isset($payslip['total_synthesis_tc']) ? number_format($payslip['total_synthesis_tc'], 2) : '0.00' ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLEAU 3: Synthèse Journalière M.C -->
        <div class="col-md-6">
            <div class="card shadow-sm payroll-card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Synthèse Journalière M.C (Maçon)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover payroll-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 100px;">
                                        <i class="fas fa-calendar-week me-1"></i>Jour
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-users me-1"></i>Volume Journalier
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-equals me-1"></i>Équivalence
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($payslip['daily_synthesis_mc']) && !empty($payslip['daily_synthesis_mc'])):
                                    foreach ($payslip['daily_synthesis_mc'] as $row):
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['day']) ?></td>
                                        <td class="text-center"><?= $row['daily_volume'] ?></td>
                                        <td class="text-center"><strong>$<?= number_format($row['equivalence'], 2) ?></strong></td>
                                    </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Aucune donnée</td>
                                    </tr>
                                <?php
                                endif;
                                ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>TOTAL</td>
                                    <td class="text-center">-</td>
                                    <td class="text-center"><strong>$<?= isset($payslip['total_synthesis_mc']) ? number_format($payslip['total_synthesis_mc'], 2) : '0.00' ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
