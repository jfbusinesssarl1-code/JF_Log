<?php
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
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
          <input type="text" id="filter_compte_releve" name="compte" class="form-control" placeholder="Compte"
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
      </div>
    </form>

    <!-- Export PDF Button - Desktop and Mobile -->
    <div class="no-print">
      <!-- Desktop version -->
      <a class="btn btn-export-pdf d-none d-md-inline-flex" 
        href="?page=releve&action=export&format=pdf&<?= http_build_query($filters) ?>"
        style="position: fixed; bottom: 20px; right: 2%; z-index: 1070;">
        <i class="bi bi-file-earmark-pdf me-2"></i> Exporter PDF
      </a>
      <!-- Mobile version -->
      <a class="btn btn-export-pdf-mobile d-md-none" 
        href="?page=releve&action=export&format=pdf&<?= http_build_query($filters) ?>"
        style="position: fixed; bottom: 80px; right: 16px; z-index: 1070;"
        title="Exporter PDF">
        <i class="bi bi-file-earmark-pdf"></i>
      </a>
    </div>

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
    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination->getTotalPages() > 1): ?>
      <nav aria-label="Pagination" class="mt-4 mb-4">
        <ul class="pagination justify-content-center">
          <li class="page-item disabled">
            <span class="page-link"><?= htmlspecialchars($pagination->getDisplayMessage()) ?></span>
          </li>
          <?php if ($pagination->hasPreviousPage()): ?>
            <li class="page-item">
              <a class="page-link"
                href="?page=releve&page_num=1&compte=<?= urlencode($filters['compte'] ?? '') ?>&lieu=<?= urlencode($filters['lieu'] ?? '') ?>&date_debut=<?= urlencode($filters['date_debut'] ?? '') ?>&date_fin=<?= urlencode($filters['date_fin'] ?? '') ?>"
                aria-label="Première"><span aria-hidden="true">&laquo;&laquo;</span></a>
            </li>
            <li class="page-item">
              <a class="page-link"
                href="?page=releve&page_num=<?= $pagination->getPreviousPage() ?>&compte=<?= urlencode($filters['compte'] ?? '') ?>&lieu=<?= urlencode($filters['lieu'] ?? '') ?>&date_debut=<?= urlencode($filters['date_debut'] ?? '') ?>&date_fin=<?= urlencode($filters['date_fin'] ?? '') ?>"
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
                  href="?page=releve&page_num=<?= $pageNum ?>&compte=<?= urlencode($filters['compte'] ?? '') ?>&lieu=<?= urlencode($filters['lieu'] ?? '') ?>&date_debut=<?= urlencode($filters['date_debut'] ?? '') ?>&date_fin=<?= urlencode($filters['date_fin'] ?? '') ?>"><?= $pageNum ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>

          <?php if ($pagination->hasNextPage()): ?>
            <li class="page-item">
              <a class="page-link"
                href="?page=releve&page_num=<?= $pagination->getNextPage() ?>&compte=<?= urlencode($filters['compte'] ?? '') ?>&lieu=<?= urlencode($filters['lieu'] ?? '') ?>&date_debut=<?= urlencode($filters['date_debut'] ?? '') ?>&date_fin=<?= urlencode($filters['date_fin'] ?? '') ?>"
                aria-label="Suivant"><span aria-hidden="true">&raquo;</span></a>
            </li>
            <li class="page-item">
              <a class="page-link"
                href="?page=releve&page_num=<?= $pagination->getTotalPages() ?>&compte=<?= urlencode($filters['compte'] ?? '') ?>&lieu=<?= urlencode($filters['lieu'] ?? '') ?>&date_debut=<?= urlencode($filters['date_debut'] ?? '') ?>&date_fin=<?= urlencode($filters['date_fin'] ?? '') ?>"
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
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var inputEl = document.getElementById('filter_compte_releve');

    AccountSearch.fetchComptes().then(function () {
      // Enrichir l'input avec le label si un code de compte est déjà présent
      var initial = inputEl.value.trim();
      if (initial) {
        // Vérifier si c'est juste un code (sans label)
        if (!initial.includes(' — ')) {
          var found = (window.comptesList || []).find(c => c.code === initial);
          if (found) {
            inputEl.value = found.code + ' — ' + (found.label || '');
          }
        }
      }

      AccountSearch.createSuggestionBox({
        inputId: 'filter_compte_releve',
        suggestionsId: 'filter_compte_releve_suggestions',
        renderItemHtml: function (c) {
          return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
        },
        onChoose: function (item) {
          if (!item) return;
          inputEl.value = item.code + ' — ' + (item.label || '');
        }
      });
    }).catch(console.error);

    // Avant soumission, extraire juste le code (avant le " — ")
    inputEl.closest('form').addEventListener('submit', function (e) {
      var val = inputEl.value.trim();
      if (val && val.includes(' — ')) {
        // Extraire juste le code
        var code = val.split(' — ')[0].trim();
        // Créer un champ hidden temporaire avec le code seul
        var tempInput = document.createElement('input');
        tempInput.type = 'hidden';
        tempInput.name = 'compte';
        tempInput.value = code;
        this.appendChild(tempInput);
        // Désactiver le champ visible pour ne pas le soumettre
        inputEl.disabled = true;
        // Réactiver après soumission (au cas où la soumission échoue)
        setTimeout(function () {
          inputEl.disabled = false;
        }, 100);
      }
    });
  });
</script>

</html>