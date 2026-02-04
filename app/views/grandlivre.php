<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Grand-Livre - Comptabilité';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <h2>Grand-Livre</h2>
    <form id="grandlivreForm" method="get" class="mb-3">
      <input type="hidden" name="page" value="grandlivre">
      <div class="row">
        <div class="col-md-4">
          <div class="position-relative">
            <input type="text" id="compte_grandlivre" class="form-control" value="<?= htmlspecialchars($selected) ?>"
              placeholder="Compte">
            <div id="compte_grandlivre_suggestions" class="list-group"
              style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;"></div>
          </div>
        </div>
        <div class="col-md-3">
          <input type="date" name="date_debut" class="form-control"
            value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <input type="date" name="date_fin" class="form-control"
            value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <button class="btn btn-secondary w-100" type="submit">Filtrer</button>
          <a class="btn btn-outline-secondary w-100 mt-2 position-fixed"
            style="max-width:150px; bottom:30px; right: 2%;"
            href="?page=grandlivre&action=export&compte=<?= urlencode($selected) ?>&date_debut=<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>&date_fin=<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">Exporter
            PDF</a>
        </div>
      </div>
    </form>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Date</th>
          <th style="width:25%">Libellé</th>
          <th>Débit</th>
          <th>Crédit</th>
          <!-- <th style="width:1%">Actions</th> -->
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($entries)):
          foreach ($entries as $entry): ?>
        <tr>
          <td><?= htmlspecialchars($entry['date'] ?? '') ?></td>
          <td><?= htmlspecialchars($entry['libelle'] ?? '') ?></td>
          <td><?= ($entry['debit'] !== '' ? '$ ' . htmlspecialchars($entry['debit']) : '') ?></td>
          <td><?= ($entry['credit'] !== '' ? '$ ' . htmlspecialchars($entry['credit']) : '') ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
      <tfoot>
        <tr class="table-secondary fw-semibold">
          <td colspan="2" class="text-end">Total</td>
          <td><?= isset($totals) ? ('$ ' . number_format($totals['debit'] ?? 0, 2)) : '' ?></td>
          <td><?= isset($totals) ? ('$ ' . number_format($totals['credit'] ?? 0, 2)) : '' ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    AccountSearch.fetchComptes().then(function() {
      // add hidden input for submission
      var parent = document.getElementById('compte_grandlivre').parentNode;
      var hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.id = 'compte_grandlivre_code';
      hidden.name = 'compte';
      hidden.value = document.getElementById('compte_grandlivre').value || '';
      parent.appendChild(hidden);
      // prefill display
      if (hidden.value) {
        var found = (window.comptesList || []).find(c => c.code === hidden.value);
        if (found) document.getElementById('compte_grandlivre').value = found.code + ' — ' + (found.label ||
          '');
      }

      AccountSearch.createSuggestionBox({
        inputId: 'compte_grandlivre',
        suggestionsId: 'compte_grandlivre_suggestions',
        renderItemHtml: function(c) {
          return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
        },
        onChoose: function(item) {
          if (!item) return;
          document.getElementById('compte_grandlivre').value = item.code + ' — ' + (item.label || '');
          document.getElementById('compte_grandlivre_code').value = item.code;
          document.getElementById('grandlivreForm').submit();
        }
      });
    }).catch(console.error);
  });
  </script>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>