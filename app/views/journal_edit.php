<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Modifier - Journal';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-secondary mb-4">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="?page=dashboard" style="margin-right:10%;">
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
    <h2 class="text-center">Modifier une opération</h2>
    <?php if ($entry): ?>
      <div class="d-flex justify-content-center">
        <form method="post" style="width:75%; min-width:300px; height:75%;">
          <div class="mb-2">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" id="date" class="form-control"
              value="<?= htmlspecialchars($entry['date'] ?? '') ?>" required>
          </div>
          <div class="mb-2">
            <label for="lieu" class="form-label">Lieu</label>
            <input type="text" name="lieu" id="lieu" class="form-control"
              value="<?= htmlspecialchars($entry['lieu'] ?? '') ?>" required>
          </div>
          <div class="mb-2">
            <label for="compte_search" class="form-label">Compte</label>
            <div class="position-relative">
              <input type="text" id="compte_search" class="form-control"
                placeholder="Rechercher un compte (code ou libellé)">
              <div id="compte_suggestions" class="list-group"
                style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;"></div>
            </div>
            <input type="hidden" name="compte" id="compte">
            <input type="text" id="compte_display" class="form-control mt-2" placeholder="Compte sélectionné" readonly>
            <div class="mt-2">
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="side" id="side_debit" value="debit">
                <label class="form-check-label" for="side_debit">Débit</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="side" id="side_credit" value="credit">
                <label class="form-check-label" for="side_credit">Crédit</label>
              </div>
            </div>
          </div>
          <div class="mb-2">
            <label for="libelle" class="form-label">Libellé</label>
            <input type="text" name="libelle" id="libelle" class="form-control"
              value="<?= htmlspecialchars($entry['libelle'] ?? '') ?>" required>
          </div>
          <div class="mb-2">
            <label for="debit" class="form-label">Débit</label>
            <input type="number" step="0.01" name="debit" id="debit" class="form-control"
              value="<?= htmlspecialchars($entry['debit'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label for="credit" class="form-label">Crédit</label>
            <input type="number" step="0.01" name="credit" id="credit" class="form-control"
              value="<?= htmlspecialchars($entry['credit'] ?? '') ?>">
          </div>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <a href="?page=journal" class="btn btn-secondary">Annuler</a>
        </form>
      <?php else: ?>
        <div class="alert alert-danger">Opération introuvable.</div>
      <?php endif; ?>
    </div>
    <script>
      const currentCompte = "<?= htmlspecialchars($entry['compte'] ?? '') ?>";

      // Use shared AccountSearch helper and add auto-detect for debit/credit side
      document.addEventListener('DOMContentLoaded', function () {
        AccountSearch.fetchComptes().then(function () {
          AccountSearch.createSuggestionBox({
            inputId: 'compte_search',
            suggestionsId: 'compte_suggestions',
            renderItemHtml: function (c) {
              return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-primary" data-action="debit">Débit</button>
                  <button type="button" class="btn btn-secondary" data-action="credit">Crédit</button>
                </div>`;
            },
            onChoose: function (item, extra) {
              const action = extra && extra.action;
              selectAccountForEdit(item.code, action);
            }
          });

          // Pre-select current account if present and show display
          if (currentCompte && document.getElementById('compte')) {
            document.getElementById('compte').value = currentCompte;
            var acc = (window.comptesList || []).find(c => c.code === currentCompte);
            if (acc) document.getElementById('compte_display').value = acc.code + ' — ' + (acc.label || '');
          }

          // auto-detect side from values
          const debitEl = document.getElementById('debit');
          const creditEl = document.getElementById('credit');
          const setSideFromValues = function () {
            const d = parseFloat(debitEl?.value || '0');
            const c = parseFloat(creditEl?.value || '0');
            if (d > 0) document.getElementById('side_debit').checked = true;
            else if (c > 0) document.getElementById('side_credit').checked = true;
          };
          setSideFromValues();
          if (debitEl) debitEl.addEventListener('input', setSideFromValues);
          if (creditEl) creditEl.addEventListener('input', setSideFromValues);
        }).catch(console.error);
      });

      function selectAccountForEdit(code, side) {
        const acc = (window.comptesList || []).find(c => c.code === code);
        if (!acc) return;
        const hidden = document.getElementById('compte');
        const debitRadio = document.getElementById('side_debit');
        const creditRadio = document.getElementById('side_credit');
        if (hidden) hidden.value = code;
        if (document.getElementById('compte_display')) document.getElementById('compte_display').value = acc.code + ' — ' + (acc.label || '');
        if (acc.intitule) {
          const intituleInput = document.getElementById('libelle');
          if (intituleInput && !intituleInput.value) intituleInput.value = acc.intitule;
        }
        if (side === 'debit' && debitRadio) debitRadio.checked = true;
        if (side === 'credit' && creditRadio) creditRadio.checked = true;
      }
    </script>
    <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>