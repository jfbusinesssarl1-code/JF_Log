<?php
require_once __DIR__ . '/../_layout_head.php';
?>

<div class="container mt-5">
    <h1>Supprimer l'Ouvrier: <?= htmlspecialchars($worker['name']) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card p-4">
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Attention !</h5>
                    <p>Êtes-vous sûr de vouloir supprimer l'ouvrier <strong><?= htmlspecialchars($worker['name']) ?></strong> ?</p>
                    <p class="mb-0">Cette action est irréversible et supprimera définitivement l'ouvrier du système.</p>
                </div>

                <div class="card-body">
                    <p><strong>Nom :</strong> <?= htmlspecialchars($worker['name']) ?></p>
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($worker['category']) ?></p>
                    <p><strong>Chantier :</strong> <?= htmlspecialchars($site['name']) ?></p>
                </div>

                <form method="POST" class="mt-3">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Oui, supprimer
                        </button>
                        <a href="?page=payroll&action=workers&site_id=<?= (string) $site['_id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../_layout_footer.php'; ?>