<?php
require_once __DIR__ . '/../_layout_head.php';

// Convertir la date au format français
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
    <h1>Saisir les Présences - <?= htmlspecialchars($site['name']) ?></h1>
    <p class="text-muted">Semaine du <?= htmlspecialchars($week_of) ?></p>
    <p class="text-muted">Période: du <?= htmlspecialchars($week_start) ?> au <?= htmlspecialchars($week_end) ?></p>

    <div class="row mb-3">
        <div class="col-md-12">
            <a href="?page=payroll&action=siteDetail&id=<?= (string) $site['_id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <!-- Navigation semaines -->
            <div class="btn-group ms-2" role="group">
                <a href="?page=payroll&action=attendance&site_id=<?= (string) $site['_id'] ?>&week_of=<?= date('Y-m-d', strtotime($week_of . ' -1 week')) ?>&week_start=<?= htmlspecialchars($week_start) ?>&week_end=<?= htmlspecialchars($week_end) ?>" 
                   class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left"></i> Semaine Précédente
                </a>
                <a href="?page=payroll&action=attendance&site_id=<?= (string) $site['_id'] ?>&week_of=<?= date('Y-m-d') ?>&week_start=<?= htmlspecialchars($week_start) ?>&week_end=<?= htmlspecialchars($week_end) ?>" 
                   class="btn btn-outline-secondary">
                    Aujourd'hui
                </a>
                <a href="?page=payroll&action=attendance&site_id=<?= (string) $site['_id'] ?>&week_of=<?= date('Y-m-d', strtotime($week_of . ' +1 week')) ?>&week_start=<?= htmlspecialchars($week_start) ?>&week_end=<?= htmlspecialchars($week_end) ?>" 
                   class="btn btn-outline-secondary">
                    Semaine Suivante <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

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

    <?php if (empty($workers)): ?>
        <div class="alert alert-warning">
            Aucun ouvrier enregistré pour ce chantier. 
            <a href="?page=payroll&action=workers&site_id=<?= (string) $site['_id'] ?>">Ajouter des ouvriers</a>
        </div>
    <?php else: ?>
        <form method="POST" class="card">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">

            <div class="row mb-3 p-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Date de début de semaine</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($week_start) ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Date de fin de semaine</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($week_end) ?>" required>
                </div>
                <div class="col-md-6 align-self-end">
                    <p class="text-muted mb-0">Ces dates seront enregistrées avec chaque présence.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="bg-dark text-white" style="font-weight: bold; font-size: 0.95rem;">
                        <tr>
                            <th style="width: 30px; text-align: center;">No</th>
                            <th style="width: 180px;">Nom</th>
                            <th style="width: 45px; text-align: center;">Cat.</th>
                            <th style="width: 90px; text-align: center;">Lund</th>
                            <th style="width: 90px; text-align: center;">Mard</th>
                            <th style="width: 90px; text-align: center;">Merc</th>
                            <th style="width: 90px; text-align: center;">Jeud</th>
                            <th style="width: 90px; text-align: center;">Vend</th>
                            <th style="width: 90px; text-align: center;">Sam</th>
                            <th style="width: 80px; text-align: center;">Jrs Prestés</th>
                            <th style="width: 110px; text-align: center;">Sal Hebdo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $index = 1;
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                        
                        // Récupérer les tarifs de salaire
                        $configModel = new \App\Models\SalaryConfigModel();
                        $salaryRates = [];
                        foreach ($workers as $worker) {
                            $category = $worker['category'];
                            if (!isset($salaryRates[$category])) {
                                $rate = ($category === 'M.C') ? $configModel->getMCDailyRate($_GET['site_id']) : $configModel->getTTDailyRate($_GET['site_id']);
                                $salaryRates[$category] = $rate;
                            }
                        }

                        foreach ($workers as $worker):
                            $workerId = (string) $worker['_id'];
                            $att = $attendances[$workerId] ?? [];
                            $category = $worker['category'];
                            $dailyRate = $salaryRates[$category] ?? 3.0;
                        ?>
                            <tr>
                                <td style="text-align: center; font-weight: bold;"><?= $index++ ?></td>
                                <td><?= htmlspecialchars($worker['name']) ?></td>
                                <td style="text-align: center;">
                                    <input type="hidden" name="category[<?= $workerId ?>]" value="<?= htmlspecialchars($worker['category']) ?>">
                                    <span class="badge bg-<?= ($worker['category'] === 'T.T') ? 'success' : 'warning' ?>" style="font-size: 0.85rem;">
                                        <?= htmlspecialchars($worker['category']) ?>
                                    </span>
                                </td>

                                <?php foreach ($days as $day): ?>
                                    <td style="text-align: center;">
                                        <select name="attendance[<?= $workerId ?>][<?= $day ?>]" class="form-select form-select attendance-select" data-worker="<?= $workerId ?>" data-rate="<?= $dailyRate ?>" data-day="<?= $day ?>" style="height: 45px; font-size: 1rem; font-weight: bold;">
                                            <option value="0" <?= ($att[$day] ?? 0) == 0 ? 'selected' : '' ?>>-</option>
                                            <option value="0.5" <?= ($att[$day] ?? 0) == 0.5 ? 'selected' : '' ?>>½</option>
                                            <option value="1" <?= ($att[$day] ?? 0) == 1 ? 'selected' : '' ?>>✓</option>
                                        </select>
                                    </td>
                                <?php endforeach; ?>

                                <td style="text-align: center; font-weight: bold;">
                                    <span class="days-worked" data-worker="<?= $workerId ?>" data-rate="<?= $dailyRate ?>">
                                        <?php
                                        $total = 0;
                                        foreach ($days as $day) {
                                            $total += $att[$day] ?? 0;
                                        }
                                        echo $total;
                                        ?>
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: bold; color: #28a745;">
                                    $<span class="weekly-salary" data-worker="<?= $workerId ?>" data-rate="<?= $dailyRate ?>">
                                        <?php
                                        $salary = 0;
                                        foreach ($days as $day) {
                                            $salary += ($att[$day] ?? 0) * $dailyRate;
                                        }
                                        echo number_format($salary, 2);
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light p-3">
                <div class="alert alert-info mb-3">
                    <strong>Légende:</strong> "-" = Absent | "½" = Demi-journée | "✓" = Journée complète
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer les Présences
                    </button>
                    <a href="?page=payroll&action=siteDetail&id=<?= (string) $site['_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Amélioration du style du tableau
const style = document.createElement('style');
style.textContent = `
    .attendance-select {
        border: 2px solid #dee2e6 !important;
        border-radius: 4px !important;
        padding: 0.5rem !important;
    }
    
    .attendance-select:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        outline: none;
    }
    
    .attendance-select:hover {
        border-color: #0d6efd !important;
    }
    
    .table thead {
        background-color: #2c3e50 !important;
    }
    
    .table thead th {
        color: #ffffff !important;
        border-bottom: 3px solid #1a1f2e !important;
        padding: 1rem 0.5rem !important;
        vertical-align: middle;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f0f3f7 !important;
    }
`;
document.head.appendChild(style);

// Calculer automatiquement les jours prestés et le salaire en temps réel
document.querySelectorAll('.attendance-select').forEach(select => {
    select.addEventListener('change', function() {
        const workerId = this.dataset.worker;
        updateWorkerStats(workerId);
    });
});

function updateWorkerStats(workerId) {
    // Récupérer tous les selects de cet ouvrier
    const selects = document.querySelectorAll(`.attendance-select[data-worker="${workerId}"]`);
    
    if (selects.length === 0) return;
    
    // Récupérer le tarif du premier select (tous les selects du même ouvrier ont le même tarif)
    const dailyRate = parseFloat(selects[0].dataset.rate || 3);
    
    let totalDays = 0;
    selects.forEach(select => {
        totalDays += parseFloat(select.value || 0);
    });
    
    // Calculer le salaire
    const weeklySalary = totalDays * dailyRate;
    
    // Mettre à jour l'affichage
    const daysSpan = document.querySelector(`.days-worked[data-worker="${workerId}"]`);
    const salarySpan = document.querySelector(`.weekly-salary[data-worker="${workerId}"]`);
    
    if (daysSpan) {
        daysSpan.textContent = totalDays.toFixed(1);
    }
    if (salarySpan) {
        salarySpan.textContent = weeklySalary.toFixed(2);
    }
}

// Animation au hover pour meilleure UX
document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#f8f9fa';
    });
    row.addEventListener('mouseleave', function() {
        this.style.backgroundColor = '';
    });
});
</script>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
