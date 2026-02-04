<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Relevé - Comptabilité';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Relevé des comptes</h2>
    <form class="row g-2 mb-3" method="get">
      <input type="hidden" name="page" value="releve">
      <div class="col-md-3">
        <div class="position-relative">
          <input type="text" id="filter_compte_releve_display" class="form-control" placeholder="Compte" value="">
          <input type="hidden" id="filter_compte_releve" name="compte"
            value="<?= htmlspecialchars($filters['compte'] ?? '') ?>">
          <div id="filter_compte_releve_suggestions" class="list-group"
            style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;"></div>
        </div>
      </div>
      <div class="col-md-3"><input type="text" class="form-control" name="lieu" placeholder="Lieu"
          value="<?= htmlspecialchars($filters['lieu'] ?? '') ?>"></div>
      <div class="col-md-2"><input type="date" class="form-control" name="date_debut"
          value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>"></div>
      <div class="col-md-2"><input type="date" class="form-control" name="date_fin"
          value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>"></div>
      <div class="col-md-2">
        <button class="btn btn-secondary w-100" type="submit">Filtrer</button>
        <a class="btn btn-outline-secondary w-100 mt-2 position-fixed" style="max-width:150px; bottom:30px; right: 2%;"
          href="?page=releve&action=export&format=pdf&compte=<?= urlencode($filters['compte'] ?? '') ?>&lieu=<?= urlencode($filters['lieu'] ?? '') ?>&date_debut=<?= urlencode($filters['date_debut'] ?? '') ?>&date_fin=<?= urlencode($filters['date_fin'] ?? '') ?>">Exporter
          PDF</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Date</th>
            <th>Lieu</th>
            <th>Compte</th>
            <th>Libellé</th>
            <th>Débit</th>
            <th>Crédit</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($entries)):
            foreach ($entries as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($e['lieu'] ?? '') ?></td>
            <td><?= htmlspecialchars($e['compte'] ?? '') ?></td>
            <td><?= htmlspecialchars($e['libelle'] ?? '') ?></td>
            <td><?= ($e['debit'] !== '' ? '$ ' . htmlspecialchars($e['debit']) : '') ?></td>
            <td><?= ($e['credit'] !== '' ? '$ ' . htmlspecialchars($e['credit']) : '') ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="6" class="text-center">Aucune donnée</td>
          </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="table-secondary fw-semibold">
            <td colspan="4" class="text-end">Total</td>
            <td><?= isset($totals) ? ('$ ' . number_format($totals['debit'] ?? 0, 2)) : '' ?></td>
            <td><?= isset($totals) ? ('$ ' . number_format($totals['credit'] ?? 0, 2)) : '' ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  AccountSearch.fetchComptes().then(function() {
    // prefill display if server provided value
    var initial = document.getElementById('filter_compte_releve').value;
    if (initial) {
      var found = (window.comptesList || []).find(c => c.code === initial);
      if (found) document.getElementById('filter_compte_releve_display').value = found.code + ' — ' + (found
        .label || '');
    }

    AccountSearch.createSuggestionBox({
      inputId: 'filter_compte_releve_display',
      suggestionsId: 'filter_compte_releve_suggestions',
      renderItemHtml: function(c) {
        return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
      },
      onChoose: function(item) {
        if (!item) return;
        document.getElementById('filter_compte_releve_display').value = item.code + ' — ' + (item.label ||
          '');
        document.getElementById('filter_compte_releve').value = item.code;
      }
    });

    document.getElementById('filter_compte_releve_display').addEventListener('input', function() {
      if (!this.value) document.getElementById('filter_compte_releve').value = '';
    });
  }).catch(console.error);
});
</script>

</html>