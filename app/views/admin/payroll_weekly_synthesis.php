<?php
require_once __DIR__ . '/../_layout_head.php';

// Déterminer la semaine actuelle pour le sélecteur
$today = date('Y-m-d');
$currentWeekOf = $week_of ?? $today;
$weekTime = strtotime($currentWeekOf);
$weekStart = date('Y-m-d', strtotime('monday this week', $weekTime));
$weekEnd = date('Y-m-d', strtotime('saturday this week', $weekTime));

// Formater les dates français
$days_fr = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi', 'Sunday' => 'Dimanche'];
$months_fr = ['01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril', '05' => 'mai', '06' => 'juin',
              '07' => 'juillet', '08' => 'août', '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre'];

$week_start_day = $days_fr[date('l', strtotime($weekStart))] ?? date('l', strtotime($weekStart));
$week_start_date = date('d', strtotime($weekStart));
$week_start_month = $months_fr[date('m', strtotime($weekStart))] ?? date('F', strtotime($weekStart));
$week_end_date = date('d', strtotime($weekEnd));
$week_end_month = $months_fr[date('m', strtotime($weekEnd))] ?? date('F', strtotime($weekEnd));
?>

<div class="container-fluid mt-4">
    <!-- En-tête du rapport -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
        <div class="row align-items-center">
            <div class="col-md-12">
                <h1 style="margin: 0 0 10px 0; font-size: 32px;">
                    <i class="fas fa-chart-bar"></i> Rapport Synthèse Hebdomadaire
                </h1>
                <p style="margin: 5px 0; font-size: 16px;">
                    <strong><?= htmlspecialchars($weekStart) ?> au <?= htmlspecialchars($weekEnd) ?></strong>
                </p>
                <p style="margin: 5px 0; font-size: 14px; opacity: 0.9;">
                    Du <?= $week_start_day ?> <?= $week_start_date ?> <?= $week_start_month ?> au <?= $week_end_date ?> <?= $week_end_month ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Sélecteur de semaine -->
    <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 6px 18px rgba(20,30,50,0.06);">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="payroll">
                <input type="hidden" name="action" value="weeklyReportSynthesis">
                
                <div class="col-md-6">
                    <label for="week_of" class="form-label fw-600" style="color: #374151;">
                        <i class="fas fa-calendar-alt"></i> Sélectionner une semaine
                    </label>
                    <input type="date" id="week_of" name="week_of" class="form-control" 
                           value="<?= htmlspecialchars($week_of) ?>" style="border: 2px solid #e5e7eb;">
                </div>
                
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Afficher
                    </button>
                    <a href="?page=payroll&action=weeklyReportSynthesis" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Semaine en cours
                    </a>
                    <a href="?page=payroll&action=exportWeeklySynthesisPDF&format=pdf&week_of=<?= htmlspecialchars($week_of ?? date('Y-m-d')) ?>" class="btn btn-success">
                        <i class="fas fa-file-pdf"></i> Exporter PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['flash_warning']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['flash_warning']); ?>
        </div>
    <?php endif; ?>

    <!-- Tableau récapitulatif des chantiers actifs -->
    <?php if (empty($sites_with_activity)): ?>
        <div class="alert alert-info alert-lg" style="border-radius: 12px; padding: 25px; text-align: center;">
            <i class="fas fa-info-circle" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
            <strong>Aucune activité</strong><br>
            <small>Aucun chantier n'a enregistré de présences pour cette semaine.</small>
        </div>
    <?php else: ?>
        <!-- Tableau récapitulatif -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 6px 18px rgba(20,30,50,0.06); overflow: hidden;">
            <div class="card-header bg-gradient-primary" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px; border-bottom: none;">
                <h5 style="margin: 0;">
                    <i class="fas fa-city"></i> Chantiers Ayant Travaillé (<?= count($sites_with_activity) ?>)
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-bottom: 2px solid #d1d5db;">
                        <tr>
                            <th style="font-weight: 600; color: #374151;">Chantier</th>
                            <th style="font-weight: 600; color: #374151;">Localisation</th>
                            <th style="text-align: center; font-weight: 600; color: #374151;">
                                <span class="badge bg-info">T.T</span>
                            </th>
                            <th style="text-align: center; font-weight: 600; color: #374151;">
                                <span class="badge bg-warning text-dark">M.C</span>
                            </th>
                            <th style="text-align: right; font-weight: 600; color: #374151;">Salaire Total</th>
                            <th style="text-align: center; font-weight: 600; color: #374151;">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rowNum = 0;
                        foreach ($sites_with_activity as $siteId => $siteWithActivity):
                            $site = $siteWithActivity['site'];
                            $payslip = $siteWithActivity['payslip'];
                            
                            // Convertir les documents MongoDB en arrays
                            $payslip = is_array($payslip) ? $payslip : iterator_to_array($payslip);
                            $payroll = $payslip['payroll'] ?? [];
                            $payroll = is_array($payroll) ? $payroll : iterator_to_array($payroll);
                            
                            $countTt = 0;
                            $countMc = 0;
                            foreach ($payroll as $worker) {
                                $worker = is_array($worker) ? $worker : iterator_to_array($worker);
                                if (($worker['category'] ?? 'T.T') === 'M.C') {
                                    $countMc++;
                                } else {
                                    $countTt++;
                                }
                            }
                            
                            $rowNum++;
                            $rowBg = ($rowNum % 2 === 0) ? '#f9fafb' : '#ffffff';
                        ?>
                            <tr style="background-color: <?= $rowBg ?>; border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 15px 12px;">
                                    <strong style="color: #1f2937;"><?= htmlspecialchars($site['name'] ?? 'N/A') ?></strong>
                                </td>
                                <td style="padding: 15px 12px; color: #6b7280;">
                                    <?= htmlspecialchars($site['location'] ?? 'N/A') ?>
                                </td>
                                <td style="text-align: center; padding: 15px 12px;">
                                    <span class="badge bg-info" style="font-size: 13px; padding: 6px 10px;">
                                        <?= $countTt ?>
                                    </span>
                                </td>
                                <td style="text-align: center; padding: 15px 12px;">
                                    <span class="badge bg-warning text-dark" style="font-size: 13px; padding: 6px 10px;">
                                        <?= $countMc ?>
                                    </span>
                                </td>
                                <td style="text-align: right; padding: 15px 12px; font-weight: bold; color: #667eea;">
                                    <?= number_format($payslip['total_salary'] ?? 0, 2) ?> $
                                </td>
                                <td style="text-align: center; padding: 15px 12px;">
                                    <a href="#site_<?= htmlspecialchars($siteId) ?>" class="btn btn-sm btn-outline-primary" 
                                       data-bs-toggle="collapse" role="button">
                                        <i class="fas fa-expand-alt"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Ligne de détail repliée -->
                            <tr>
                                <td colspan="6" style="padding: 0; border: none;">
                                    <div class="collapse" id="site_<?= htmlspecialchars($siteId) ?>">
                                        <div style="padding: 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                                            <!-- Synthèse rapide -->
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div style="background: linear-gradient(135deg, #e0f2fe 0%, #cffafe 100%); border: 2px solid #0284c7; border-radius: 8px; padding: 15px;">
                                                        <h6 style="margin-bottom: 12px; color: #0c4a6e; font-weight: 600;">
                                                            <i class="fas fa-hard-hat"></i> T.T (Tout Travaux)
                                                        </h6>
                                                        <p style="margin: 5px 0; color: #475569;">
                                                            <span style="font-weight: 600;">Ouvriers :</span> <strong><?= $countTt ?></strong>
                                                        </p>
                                                        <p style="margin: 5px 0; color: #475569;">
                                                            <span style="font-weight: 600;">Salaire :</span> 
                                                            <strong style="color: #0284c7;">
                                                                <?= number_format($payslip['total_synthesis_tc'] ?? 0, 2) ?> $
                                                            </strong>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #d97706; border-radius: 8px; padding: 15px;">
                                                        <h6 style="margin-bottom: 12px; color: #78350f; font-weight: 600;">
                                                            <i class="fas fa-hammer"></i> M.C (Maçons)
                                                        </h6>
                                                        <p style="margin: 5px 0; color: #475569;">
                                                            <span style="font-weight: 600;">Ouvriers :</span> <strong><?= $countMc ?></strong>
                                                        </p>
                                                        <p style="margin: 5px 0; color: #475569;">
                                                            <span style="font-weight: 600;">Salaire :</span> 
                                                            <strong style="color: #d97706;">
                                                                <?= number_format($payslip['total_synthesis_mc'] ?? 0, 2) ?> $
                                                            </strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tableau détaillé -->
                                            <div class="table-responsive">
                                                <table class="table table-sm" style="font-size: 13px; margin-bottom: 0;">
                                                    <thead style="background: #f3f4f6; border-bottom: 2px solid #d1d5db;">
                                                        <tr>
                                                            <th style="padding: 10px; font-weight: 600;">N°</th>
                                                            <th style="padding: 10px; font-weight: 600;">Nom</th>
                                                            <th style="padding: 10px; font-weight: 600;">Cat.</th>
                                                            <th style="padding: 10px; text-align: center; font-weight: 600;">Lun</th>
                                                            <th style="padding: 10px; text-align: center; font-weight: 600;">Mar</th>
                                                            <th style="padding: 10px; text-align: center; font-weight: 600;">Mer</th>
                                                            <th style="padding: 10px; text-align: center; font-weight: 600;">Jeu</th>
                                                            <th style="padding: 10px; text-align: center; font-weight: 600;">Ven</th>
                                                            <th style="padding: 10px; text-align: center; font-weight: 600;">Sam</th>
                                                            <th style="padding: 10px; text-align: center; font-weight: 600;">Jours</th>
                                                            <th style="padding: 10px; text-align: right; font-weight: 600;">Salaire</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $count = 1;
                                                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                                                        
                                                        foreach ($payroll as $worker):
                                                            $worker = is_array($worker) ? $worker : iterator_to_array($worker);
                                                            $isMc = ($worker['category'] ?? 'T.T') === 'M.C';
                                                            $rowStyle = $isMc ? 'background: #fffbeb; border-bottom: 1px solid #fde68a;' : 'background: white; border-bottom: 1px solid #e5e7eb;';
                                                        ?>
                                                            <tr style="<?= $rowStyle ?>">
                                                                <td style="padding: 8px 10px;"><?= $count ?></td>
                                                                <td style="padding: 8px 10px;"><strong><?= htmlspecialchars($worker['worker_name'] ?? 'N/A') ?></strong></td>
                                                                <td style="padding: 8px 10px;">
                                                                    <span class="badge <?= $isMc ? 'bg-warning text-dark' : 'bg-info' ?>">
                                                                        <?= htmlspecialchars($worker['category'] ?? 'T.T') ?>
                                                                    </span>
                                                                </td>
                                                                <?php foreach ($days as $day): ?>
                                                                    <td style="padding: 8px 10px; text-align: center;">
                                                                        <?php
                                                                        $value = $worker[$day] ?? 0;
                                                                        echo ($value == 0) ? '<span style="color: #d13438;">—</span>' : htmlspecialchars($value);
                                                                        ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                                <td style="padding: 8px 10px; text-align: center;">
                                                                    <strong><?= number_format($worker['days_worked'] ?? 0, 1) ?> j</strong>
                                                                </td>
                                                                <td style="padding: 8px 10px; text-align: right;">
                                                                    <strong style="color: <?= $isMc ? '#d97706' : '#0284c7' ?>;">
                                                                        <?= number_format($worker['weekly_salary'] ?? 0, 2) ?> $
                                                                    </strong>
                                                                </td>
                                                            </tr>
                                                            <?php $count++; ?>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Résumé consolidé -->
        <div class="card" style="border-radius: 12px; border: none; box-shadow: 0 6px 18px rgba(20,30,50,0.06); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-bottom: none;">
                <h5 style="margin: 0;">
                    <i class="fas fa-chart-pie"></i> Résumé Consolidé de la Semaine
                </h5>
            </div>
            <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px;">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; padding: 15px; backdrop-filter: blur(10px);">
                            <p style="margin: 0 0 8px 0; opacity: 0.9; font-size: 13px;">
                                <i class="fas fa-city"></i> Chantiers actifs
                            </p>
                            <h4 style="margin: 0; font-size: 28px; font-weight: bold;">
                                <?= count($sites_with_activity) ?>
                            </h4>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; padding: 15px; backdrop-filter: blur(10px);">
                            <p style="margin: 0 0 8px 0; opacity: 0.9; font-size: 13px;">
                                <i class="fas fa-users"></i> Total ouvriers
                            </p>
                            <h4 style="margin: 0; font-size: 28px; font-weight: bold;">
                                <?= $weekly_totals['summary_by_category']['T.T']['workers'] + $weekly_totals['summary_by_category']['M.C']['workers'] ?>
                            </h4>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; padding: 15px; backdrop-filter: blur(10px);">
                            <p style="margin: 0 0 8px 0; opacity: 0.9; font-size: 13px;">
                                <i class="fas fa-dollar-sign"></i> Salaire total
                            </p>
                            <h4 style="margin: 0; font-size: 28px; font-weight: bold;">
                                <?= number_format($weekly_totals['total_salary'], 2) ?> $
                            </h4>
                        </div>
                    </div>
                </div>

                <!-- Détail par catégorie -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div style="background: rgba(255,255,255,0.15); border-radius: 8px; padding: 15px;">
                            <h6 style="margin: 0 0 15px 0; font-weight: 600;">
                                <i class="fas fa-hard-hat"></i> T.T (Tout Travaux)
                            </h6>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <p style="margin: 0 0 3px 0; opacity: 0.9; font-size: 13px;">Ouvriers</p>
                                    <p style="margin: 0; font-size: 18px; font-weight: bold;">
                                        <?= $weekly_totals['summary_by_category']['T.T']['workers'] ?>
                                    </p>
                                </div>
                                <div>
                                    <p style="margin: 0 0 3px 0; opacity: 0.9; font-size: 13px;">Jours travaillés</p>
                                    <p style="margin: 0; font-size: 18px; font-weight: bold;">
                                        <?= number_format($weekly_totals['summary_by_category']['T.T']['days'], 1) ?> j
                                    </p>
                                </div>
                                <div style="grid-column: 1/-1;">
                                    <p style="margin: 0 0 3px 0; opacity: 0.9; font-size: 13px;">Salaire total</p>
                                    <p style="margin: 0; font-size: 18px; font-weight: bold;">
                                        <?= number_format($weekly_totals['summary_by_category']['T.T']['salary'], 2) ?> $
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div style="background: rgba(255,255,255,0.15); border-radius: 8px; padding: 15px;">
                            <h6 style="margin: 0 0 15px 0; font-weight: 600;">
                                <i class="fas fa-hammer"></i> M.C (Maçons)
                            </h6>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <p style="margin: 0 0 3px 0; opacity: 0.9; font-size: 13px;">Ouvriers</p>
                                    <p style="margin: 0; font-size: 18px; font-weight: bold;">
                                        <?= $weekly_totals['summary_by_category']['M.C']['workers'] ?>
                                    </p>
                                </div>
                                <div>
                                    <p style="margin: 0 0 3px 0; opacity: 0.9; font-size: 13px;">Jours travaillés</p>
                                    <p style="margin: 0; font-size: 18px; font-weight: bold;">
                                        <?= number_format($weekly_totals['summary_by_category']['M.C']['days'], 1) ?> j
                                    </p>
                                </div>
                                <div style="grid-column: 1/-1;">
                                    <p style="margin: 0 0 3px 0; opacity: 0.9; font-size: 13px;">Salaire total</p>
                                    <p style="margin: 0; font-size: 18px; font-weight: bold;">
                                        <?= number_format($weekly_totals['summary_by_category']['M.C']['salary'], 2) ?> $
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
