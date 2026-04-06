<?php
require_once __DIR__ . '/../_layout_head.php';
?>

<div class="container mt-5">
    <h1>Configuration des Salaires - <?= htmlspecialchars($site['name']) ?></h1>

    <div class="row mb-3">
        <div class="col-md-12">
            <a href="?page=payroll&action=siteDetail&id=<?= (string) $site['_id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" class="card p-4">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">

                <div class="alert alert-info">
                    <strong>Note:</strong> Les tarifs de demi-journée seront automatiquement définis à la moitié du tarif journalier.
                </div>

                <!-- Configuration T.T -->
                <h4 class="mb-3">Ouvrier T.T (Tout Travaux)</h4>
                <div class="card mb-4 border-success">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="TT_daily_rate" class="form-label">Tarif Journalier ($) *</label>
                                    <input type="number" class="form-control" id="TT_daily_rate" name="TT_daily_rate" 
                                           value="<?= $configs['T.T']['daily_rate'] ?? 3.0 ?>" step="0.01" min="0" required>
                                    <small class="text-muted">Tarif par défaut: 3$ (1.5$ demi-journée)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Tarif Demi-Journée ($)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" 
                                               value="<?= ($configs['T.T']['daily_rate'] ?? 3.0) / 2 ?>" disabled>
                                        <span class="input-group-text">(calculé)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary mb-0">
                            <strong>Calcul du salaire:</strong>
                            <ul class="mb-0 mt-2">
                                <li>1 journée complète = <?= $configs['T.T']['daily_rate'] ?? 3.0 ?>$</li>
                                <li>1 demi-journée = <?= ($configs['T.T']['daily_rate'] ?? 3.0) / 2 ?>$</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Configuration M.C -->
                <h4 class="mb-3">Ouvrier M.C (Maçon)</h4>
                <div class="card mb-4 border-warning">
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <strong>Note:</strong> Certains chantiers peuvent avoir des tarifs différents (6$, 7$, ou convention spéciale).
                        </p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="MC_daily_rate" class="form-label">Tarif Journalier ($) *</label>
                                    <input type="number" class="form-control" id="MC_daily_rate" name="MC_daily_rate" 
                                           value="<?= $configs['M.C']['daily_rate'] ?? 6.0 ?>" step="0.01" min="0" required>
                                    <small class="text-muted">Exemple: 6$ ou 7$ selon le chantier</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Tarif Demi-Journée ($)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" 
                                               value="<?= ($configs['M.C']['daily_rate'] ?? 6.0) / 2 ?>" disabled>
                                        <span class="input-group-text">(calculé)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary mb-0">
                            <strong>Tarifs disponibles:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Option 1: <?= $configs['M.C']['daily_rate'] ?? 6.0 ?>$ par jour (<?= ($configs['M.C']['daily_rate'] ?? 6.0) / 2 ?>$ demi-journée)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer la Configuration
                    </button>
                    <a href="?page=payroll&action=siteDetail&id=<?= (string) $site['_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Résumé à droite -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Résumé de la Configuration</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-success mb-2">Tout Travaux (T.T)</h6>
                    <p class="mb-1">
                        <strong>Journée:</strong> <?= $configs['T.T']['daily_rate'] ?? 3.0 ?>$
                    </p>
                    <p class="mb-3">
                        <strong>Demi-journée:</strong> <?= ($configs['T.T']['daily_rate'] ?? 3.0) / 2 ?>$
                    </p>

                    <h6 class="text-warning mb-2">Maçon (M.C)</h6>
                    <p class="mb-1">
                        <strong>Journée:</strong> <?= $configs['M.C']['daily_rate'] ?? 6.0 ?>$
                    </p>
                    <p class="mb-0">
                        <strong>Demi-journée:</strong> <?= ($configs['M.C']['daily_rate'] ?? 6.0) / 2 ?>$
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mettre à jour les tarifs de demi-journée en temps réel
document.getElementById('TT_daily_rate').addEventListener('input', function() {
    const halfDay = (parseFloat(this.value) || 0) / 2;
    document.querySelector('[data-halfday-tt]').textContent = halfDay.toFixed(2) + '$';
});

document.getElementById('MC_daily_rate').addEventListener('input', function() {
    const halfDay = (parseFloat(this.value) || 0) / 2;
    document.querySelector('[data-halfday-mc]').textContent = halfDay.toFixed(2) + '$';
});
</script>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
