<?php
require_once __DIR__ . '/../_layout_head.php';
include __DIR__ . '/../navbar.php';
?>

<div class="container mt-5">
    <h1><?= ($action === 'create') ? 'Créer un Chantier' : 'Éditer le Chantier: ' . htmlspecialchars($site['name'] ?? '') ?></h1>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" class="card p-4">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">

                <div class="form-group mb-3">
                    <label for="name" class="form-label">Nom du Chantier *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($site['name'] ?? '') ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="location" class="form-label">Localisation</label>
                    <input type="text" class="form-control" id="location" name="location" 
                           value="<?= htmlspecialchars($site['location'] ?? '') ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="engineer_name" class="form-label">Ingénieur (Ir.)</label>
                    <input type="text" class="form-control" id="engineer_name" name="engineer_name" 
                           value="<?= htmlspecialchars($site['engineer_name'] ?? '') ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="engineer_phone" class="form-label">Téléphone Ingénieur (Ir.)</label>
                    <input type="text" class="form-control" id="engineer_phone" name="engineer_phone" 
                           value="<?= htmlspecialchars($site['engineer_phone'] ?? '') ?>" placeholder="Ex : +33 6 12 34 56 78">
                </div>

                <div class="form-group mb-3">
                    <label for="warehouse_manager_name" class="form-label">Magasinier (Mag)</label>
                    <input type="text" class="form-control" id="warehouse_manager_name" name="warehouse_manager_name" 
                           value="<?= htmlspecialchars($site['warehouse_manager_name'] ?? '') ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($site['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-control" id="status" name="status">
                        <option value="active" <?= ($site['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactive" <?= ($site['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="?page=payroll&action=sites" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
