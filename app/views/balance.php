<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Balance - Comptabilité';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <h2>Balance Comptable</h2>
    <form class="row g-2 mb-3" method="get">
      <input type="hidden" name="page" value="balance">
      <div class="col-md-4">
        <div class="position-relative">
          <input type="text" id="filter_compte_balance_display" class="form-control" placeholder="Compte" value="">
          <input type="hidden" id="filter_compte_balance" name="compte"
            value="<?= htmlspecialchars($_GET['compte'] ?? '') ?>">
          <div id="filter_compte_balance_suggestions" class="list-group"
            style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;"></div>
        </div>
      </div>
      <div class="col-md-3"><input type="date" class="form-control" name="date_debut"
          value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>"></div>
      <div class="col-md-3"><input type="date" class="form-control" name="date_fin"
          value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>"></div>
      <div class="col-md-2">
        <button class="btn btn-secondary w-100" type="submit">Filtrer</button>
        <a class="btn btn-outline-secondary w-100 mt-2 position-fixed" style="max-width:150px; bottom:30px; right: 2%;"
          href="?page=balance&action=export&format=pdf&compte=<?= urlencode($_GET['compte'] ?? '') ?>&date_debut=<?= urlencode($_GET['date_debut'] ?? '') ?>&date_fin=<?= urlencode($_GET['date_fin'] ?? '') ?>">Exporter
          PDF</a>
      </div>
    </form>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Compte</th>
          <th>Débit</th>
          <th>Crédit</th>
          <th>Solde</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($balances)):
          foreach ($balances as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['_id'] ?? '') ?></td>
          <td><?= '$ ' . htmlspecialchars($b['debit'] ?? 0) ?></td>
          <td><?= '$ ' . htmlspecialchars($b['credit'] ?? 0) ?></td>
          <td><?= '$ ' . htmlspecialchars(($b['debit'] ?? 0) - ($b['credit'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    AccountSearch.fetchComptes().then(function() {
      var initial = document.getElementById('filter_compte_balance').value;
      if (initial) {
        var found = (window.comptesList || []).find(c => c.code === initial);
        if (found) document.getElementById('filter_compte_balance_display').value = found.code + ' — ' + (found
          .label || '');
      }
      AccountSearch.createSuggestionBox({
        inputId: 'filter_compte_balance_display',
        suggestionsId: 'filter_compte_balance_suggestions',
        renderItemHtml: function(c) {
          return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
        },
        onChoose: function(item) {
          if (!item) return;
          document.getElementById('filter_compte_balance_display').value = item.code + ' — ' + (item
            .label || '');
          document.getElementById('filter_compte_balance').value = item.code;
        }
      });
      document.getElementById('filter_compte_balance_display').addEventListener('input', function() {
        if (!this.value) document.getElementById('filter_compte_balance').value = '';
      });
    }).catch(console.error);
  });
  </script>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>