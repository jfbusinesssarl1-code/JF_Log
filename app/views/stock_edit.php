<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier une opération de stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="?page=dashboard"
                style="margin-right:10%;">
                Compta
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- <div class="collapse navbar-collapse" id="navbarNav"> -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="?page=journal">Journal</a></li>
                <li class="nav-item"><a class="nav-link" href="?page=grandlivre">Grand-Livre</a></li>
                <li class="nav-item"><a class="nav-link" href="?page=balance">Balance</a></li>
                <li class="nav-item"><a class="nav-link" href="?page=stock">Fiche de Stock</a></li>
                <li class="nav-item"><a class="nav-link" href="?page=releve">Relevé</a></li>
                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link text-success fw-bold" href="?page=register">⚙️ Gestion
                            Utilisateurs</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><span class="nav-link">👤
                        <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?>
                        (<?= htmlspecialchars($_SESSION['user']['role'] ?? 'user') ?>)</span></li>
                <li class="nav-item"><a class="nav-link text-danger fw-bold" href="?page=logout">Déconnexion</a></li>
            </ul>
            <!-- </div> -->
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Modifier une opération de stock</h2>
        <form method="post" action="?page=stock&action=edit&id=<?= urlencode($entry['_id']) ?>" class="mt-4">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control"
                        value="<?= htmlspecialchars($entry['date'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="operation" class="form-label">Opération</label>
                    <select name="operation" id="operation" class="form-select" required>
                        <option value="entree" <?= ($entry['entree']['qte'] ?? 0) > 0 ? 'selected' : '' ?>>Entrée
                        </option>
                        <option value="sortie" <?= ($entry['sortie']['qte'] ?? 0) > 0 ? 'selected' : '' ?>>Sortie
                        </option>
                    </select>
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label for="compte" class="form-label">Compte</label>
                    <input type="text" name="compte" id="compte" class="form-control"
                        value="<?= htmlspecialchars($entry['compte'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="intitule" class="form-label">Intitulé compte</label>
                    <input type="text" name="intitule" id="intitule" class="form-control"
                        value="<?= htmlspecialchars($entry['intitule'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label for="lieu" class="form-label">Lieu</label>
                    <input type="text" name="lieu" id="lieu" class="form-control"
                        value="<?= htmlspecialchars($entry['lieu'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label for="designation" class="form-label">Désignation</label>
                    <input type="text" name="designation" id="designation" class="form-control"
                        value="<?= htmlspecialchars($entry['designation'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="quantite" class="form-label">Quantité</label>
                    <input type="number" step="0.01" name="quantite" id="quantite" class="form-control"
                        value="<?= ($entry['entree']['qte'] ?? $entry['sortie']['qte'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <label for="pu" class="form-label">Prix Unitaire</label>
                    <input type="number" step="0.01" name="pu" id="pu" class="form-control"
                        value="<?= ($entry['entree']['pu'] ?? $entry['sortie']['pu'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">Enregistrer</button>
                </div>
            </div>
        </form>
        <a href="?page=stock" class="btn btn-secondary mt-3">Retour à la fiche de stock</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>