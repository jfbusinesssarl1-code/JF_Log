<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php $title = 'Modifier - Stock';
    require __DIR__ . '/_layout_head.php'; ?>
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
                    <label for="compte_search" class="form-label">Compte</label>
                    <div class="position-relative">
                        <input type="text" id="compte_search" class="form-control"
                            placeholder="Rechercher un compte (code ou libellé)">
                        <div id="compte_suggestions" class="list-group"
                            style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;">
                        </div>
                    </div>
                    <input type="hidden" name="compte" id="compte">
                    <input type="text" id="compte_display" class="form-control mt-2" placeholder="Compte sélectionné"
                        readonly>
                </div>
                <div class="col-md-6">
                    <label for="intitule" class="form-label">Intitulé compte</label>
                    <input type="text" name="intitule" id="intitule" class="form-control"
                        value="<?= htmlspecialchars($entry['intitule'] ?? '') ?>" required readonly>
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
    <script>
        const currentCompte = "<?= htmlspecialchars($entry['compte'] ?? '') ?>";

        // Use shared AccountSearch and pre-fill if possible
        document.addEventListener('DOMContentLoaded', function () {
            AccountSearch.fetchComptes().then(function () {
                AccountSearch.createSuggestionBox({
                    inputId: 'compte_search',
                    suggestionsId: 'compte_suggestions',
                    renderItemHtml: function (c) {
                        return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-primary" data-action="choose">Choisir</button>
                            </div>`;
                    },
                    onChoose: function (item) {
                        if (!item) return;
                        document.getElementById('compte').value = item.code;
                        document.getElementById('compte_display').value = item.code + ' — ' + (item.label || '');
                        document.getElementById('intitule').value = item.intitule || '';
                    }
                });

                // Pre-fill currentCompte if available
                if (currentCompte) {
                    const acc = (window.comptesList || []).find(c => c.code === currentCompte);
                    if (acc) {
                        document.getElementById('compte').value = acc.code;
                        document.getElementById('intitule').value = acc.intitule || '';
                        document.getElementById('compte_display').value = acc.code + ' — ' + (acc.label || '');
                    }
                }
            }).catch(console.error);
        });

        // Helpers
        function escapeHtml(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
        function escapeAttr(s) { return String(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

        function updateIntitule() {
            const intituleInput = document.getElementById('intitule');
            const compteEl = document.getElementById('compte');
            if (!compteEl || !intituleInput) return;
            // If the element is a select (legacy), read dataset on selected option
            if (compteEl.tagName && compteEl.tagName.toLowerCase() === 'select') {
                const selected = compteEl.options[compteEl.selectedIndex];
                if (selected && selected.value && selected.dataset.intitule) {
                    intituleInput.value = selected.dataset.intitule;
                    return;
                }
            }
            // Otherwise, lookup by code in the loaded comptes list
            const code = compteEl.value;
            if (code) {
                const acc = (window.comptesList || []).find(c => c.code === code);
                intituleInput.value = acc ? (acc.intitule || '') : '';
            } else {
                intituleInput.value = '';
            }
        }
    </script>
    <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>