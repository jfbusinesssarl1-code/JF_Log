<?php
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
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
      </div>
    </form>

    <!-- Export PDF Button - Desktop and Mobile -->
    <div class="no-print">
      <!-- Desktop version -->
      <a class="btn btn-export-pdf d-none d-md-inline-flex" 
        href="?page=balance&action=export&format=pdf&<?= http_build_query($filters) ?>"
        style="position: fixed; bottom: 20px; right: 2%; z-index: 1070;">
        <i class="bi bi-file-earmark-pdf me-2"></i> Exporter PDF
      </a>
      <!-- Mobile version -->
      <a class="btn btn-export-pdf-mobile d-md-none" 
        href="?page=balance&action=export&format=pdf&<?= http_build_query($filters) ?>"
        style="position: fixed; bottom: 80px; right: 16px; z-index: 1070;"
        title="Exporter PDF">
        <i class="bi bi-file-earmark-pdf"></i>
      </a>
    </div>

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
      <tfoot>
        <tr class="table-secondary fw-semibold">
          <td class="text-end">Total</td>
          <td><?= isset($totals) ? ('$ ' . number_format($totals['debit'] ?? 0, 2)) : '' ?></td>
          <td><?= isset($totals) ? ('$ ' . number_format($totals['credit'] ?? 0, 2)) : '' ?></td>
          <td><?= isset($totals) ? ('$ ' . number_format($totals['solde'] ?? 0, 2)) : '' ?></td>
        </tr>
      </tfoot>
    </table>
    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination->getTotalPages() > 1): ?>
      <nav aria-label="Pagination" class="mt-4 mb-4">
        <ul class="pagination justify-content-center">
          <li class="page-item disabled">
            <span class="page-link"><?= htmlspecialchars($pagination->getDisplayMessage()) ?></span>
          </li>
          <?php $fcomp = urlencode($filters['compte'] ?? ($_GET['compte'] ?? ''));
          $fd1 = urlencode($filters['date_debut'] ?? ($_GET['date_debut'] ?? ''));
          $fd2 = urlencode($filters['date_fin'] ?? ($_GET['date_fin'] ?? '')); ?>
          <?php if ($pagination->hasPreviousPage()): ?>
            <li class="page-item">
              <a class="page-link"
                href="?page=balance&page_num=1&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Première"><span aria-hidden="true">&laquo;&laquo;</span></a>
            </li>
            <li class="page-item">
              <a class="page-link"
                href="?page=balance&page_num=<?= $pagination->getPreviousPage() ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Précédent"><span aria-hidden="true">&laquo;</span></a>
            </li>
          <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&laquo;&laquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
          <?php endif; ?>

          <?php foreach ($pagination->getPageNumbers(2) as $pageNum): ?>
            <?php if ($pageNum === '...'): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php elseif ($pageNum == $pagination->getCurrentPage()): ?>
              <li class="page-item active"><span class="page-link"><?= $pageNum ?></span></li>
            <?php else: ?>
              <li class="page-item"><a class="page-link"
                  href="?page=balance&page_num=<?= $pageNum ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"><?= $pageNum ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>

          <?php if ($pagination->hasNextPage()): ?>
            <li class="page-item">
              <a class="page-link"
                href="?page=balance&page_num=<?= $pagination->getNextPage() ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Suivant"><span aria-hidden="true">&raquo;</span></a>
            </li>
            <li class="page-item">
              <a class="page-link"
                href="?page=balance&page_num=<?= $pagination->getTotalPages() ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Dernière"><span aria-hidden="true">&raquo;&raquo;</span></a>
            </li>
          <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      AccountSearch.fetchComptes().then(function () {
        var initial = document.getElementById('filter_compte_balance').value;
        if (initial) {
          var found = (window.comptesList || []).find(c => c.code === initial);
          if (found) document.getElementById('filter_compte_balance_display').value = found.code + ' — ' + (found
            .label || '');
        }
        AccountSearch.createSuggestionBox({
          inputId: 'filter_compte_balance_display',
          suggestionsId: 'filter_compte_balance_suggestions',
          renderItemHtml: function (c) {
            return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
          },
          onChoose: function (item) {
            if (!item) return;
            document.getElementById('filter_compte_balance_display').value = item.code + ' — ' + (item
              .label || '');
            document.getElementById('filter_compte_balance').value = item.code;
          }
        });
        document.getElementById('filter_compte_balance_display').addEventListener('input', function () {
          if (!this.value) document.getElementById('filter_compte_balance').value = '';
        });
      }).catch(console.error);
    });
  </script>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>