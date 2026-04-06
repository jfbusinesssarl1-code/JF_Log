<?php
require_once __DIR__ . '/../_layout_head.php';
?>

<div class="container mt-5">
    <h1><?= ($action === 'create') ? 'Ajouter un Ouvrier' : 'Éditer l\'Ouvrier: ' . htmlspecialchars($worker['name'] ?? '') ?></h1>

    <div class="row">
        <div class="col-md-6">
            <form method="POST" class="card p-4">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">

                <div class="form-group mb-3">
                    <label for="name" class="form-label">Nom Complet *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($worker['name'] ?? '') ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="category" class="form-label">Catégorie *</label>
                    <select class="form-control" id="category" name="category" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="T.T" <?= ($worker['category'] ?? '') === 'T.T' ? 'selected' : '' ?>>
                            T.T (Tout Travaux)
                        </option>
                        <option value="M.C" <?= ($worker['category'] ?? '') === 'M.C' ? 'selected' : '' ?>>
                            M.C (Maçon)
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="?page=payroll&action=workers&site_id=<?= (string) $site['_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>
