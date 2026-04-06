<?php
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Dashboard - Comptabilité';
  require __DIR__ . '/_layout_head.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <h2>Tableau de bord</h2>
    <form class="row mb-4" id="dashboardFilters" method="get">
      <input type="hidden" name="page" value="dashboard">
      <div class="col-md-3">
        <label for="filterCompte" class="form-label">Compte</label>
        <div class="position-relative">
          <input type="text" id="filterCompte" class="form-control" placeholder="Ex: 601">
          <input type="hidden" id="filterCompte_code" name="compte"
            value="<?= htmlspecialchars($_GET['compte'] ?? '') ?>">
          <div id="filter_compte_suggestions" class="list-group"
            style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;"></div>
        </div>
      </div>
      <div class="col-md-3">
        <label for="filterStart" class="form-label">Début</label>
        <input type="date" id="filterStart" name="date_debut" class="form-control"
          value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label for="filterEnd" class="form-label">Fin</label>
        <input type="date" id="filterEnd" name="date_fin" class="form-control"
          value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <a class="btn btn-outline-secondary w-100" href="?page=dashboard">Tout</a>
      </div>
    </form>
    <div class="row">
      <div class="col-md-6">
        <h4>Balance par compte</h4>
        <canvas id="balanceChart"></canvas>
      </div>
      <div class="col-md-6">
        <h4>Débits/Crédits par mois</h4>
        <canvas id="journalChart"></canvas>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h4>Écritures détaillées (filtrées)</h4>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-success table-striped table-hover" id="detailTable">
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
              <?php if (!empty($journal_paged) && is_array($journal_paged)):
                foreach ($journal_paged as $e): ?>
              <tr>
                <td><?= htmlspecialchars($e['date'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['lieu'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['compte'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['libelle'] ?? '') ?></td>
                <td class="text-end"><?= ($e['debit'] !== '' ? '$ ' . htmlspecialchars($e['debit']) : '') ?></td>
                <td class="text-end"><?= ($e['credit'] !== '' ? '$ ' . htmlspecialchars($e['credit']) : '') ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr>
                <td colspan="6" class="text-center">Aucune donnée</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <?php if (isset($pagination) && $pagination->getTotalPages() > 1): ?>
        <nav aria-label="Pagination" class="mt-3 mb-3">
          <ul class="pagination justify-content-center">
            <li class="page-item disabled"><span
                class="page-link"><?= htmlspecialchars($pagination->getDisplayMessage()) ?></span></li>
            <?php $fcomp = urlencode($filters['compte'] ?? ($_GET['compte'] ?? ''));
              $fd1 = urlencode($filters['date_debut'] ?? ($_GET['date_debut'] ?? ''));
              $fd2 = urlencode($filters['date_fin'] ?? ($_GET['date_fin'] ?? '')); ?>
            <?php if ($pagination->hasPreviousPage()): ?>
            <li class="page-item"><a class="page-link"
                href="?page=dashboard&page_num=1&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Première"><span aria-hidden="true">&laquo;&laquo;</span></a></li>
            <li class="page-item"><a class="page-link"
                href="?page=dashboard&page_num=<?= $pagination->getPreviousPage() ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Précédent"><span aria-hidden="true">&laquo;</span></a></li>
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
                href="?page=dashboard&page_num=<?= $pageNum ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"><?= $pageNum ?></a>
            </li>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($pagination->hasNextPage()): ?>
            <li class="page-item"><a class="page-link"
                href="?page=dashboard&page_num=<?= $pagination->getNextPage() ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Suivant"><span aria-hidden="true">&raquo;</span></a></li>
            <li class="page-item"><a class="page-link"
                href="?page=dashboard&page_num=<?= $pagination->getTotalPages() ?>&compte=<?= $fcomp ?>&date_debut=<?= $fd1 ?>&date_fin=<?= $fd2 ?>"
                aria-label="Dernière"><span aria-hidden="true">&raquo;&raquo;</span></a></li>
            <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&raquo;&raquo;</span></li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <script>
  console.log('🔍 Dashboard init...');
  const balances = <?= json_encode($balances ?? [], JSON_HEX_TAG) ?>;
  const journal = <?= json_encode($journal ?? [], JSON_HEX_TAG) ?>;
  console.log('✅ Balances loaded:', balances.length, 'items');
  console.log('✅ Journal loaded:', journal.length, 'items');
  let balanceChart, journalChart, filteredJournalGlobal = [];

  window.addEventListener('error', function(e) {
    console.error('❌ JS Error:', e.message, 'at', e.filename + ':' + e.lineno);
  });

  window.addEventListener('unhandledrejection', function(e) {
    console.error('❌ Promise Rejection:', e.reason);
  });

  function updateDashboard() {
    const codeEl = document.getElementById('filterCompte_code');
    const compte = codeEl ? codeEl.value : document.getElementById('filterCompte').value;
    const start = document.getElementById('filterStart').value;
    const end = document.getElementById('filterEnd').value;
    
    // Filtre journal - avec comparaison de dates correctes
    let filteredJournal = journal.filter(e => {
      if (compte && e.compte !== compte) return false;
      if (start && (!e.date || e.date < start)) return false;
      if (end && (!e.date || e.date > end)) return false;
      return true;
    });
    filteredJournalGlobal = filteredJournal.filter(e => (e.debit || 0) > 0);
    
    // Filtre balances - seulement si il y a un compte spécifique sinon afficher tous
    let filteredBalances = balances.filter(b => {
      if (!compte) return true;
      return b._id === compte;
    });
    
    // Balance chart
    const balanceLabels = filteredBalances.map(b => b._id);
    const balanceDebits = filteredBalances.map(b => b.debit);
    const balanceCredits = filteredBalances.map(b => b.credit);
    balanceChart.data.labels = balanceLabels;
    balanceChart.data.datasets[0].data = balanceDebits;
    balanceChart.data.datasets[1].data = balanceCredits;
    balanceChart.update();
    
    // Journal chart - groupé par mois
    const months = {};
    filteredJournal.forEach(e => {
      if (!e.date) return;
      const m = e.date.substring(0, 7);
      if (!months[m]) months[m] = {
        debit: 0,
        credit: 0
      };
      months[m].debit += e.debit || 0;
      months[m].credit += e.credit || 0;
    });
    const monthLabels = Object.keys(months).sort();
    const monthDebits = monthLabels.map(m => months[m].debit);
    const monthCredits = monthLabels.map(m => months[m].credit);
    journalChart.data.labels = monthLabels;
    journalChart.data.datasets[0].data = monthDebits;
    journalChart.data.datasets[1].data = monthCredits;
    journalChart.update();
  }

  window.addEventListener('DOMContentLoaded', () => {
    // Init charts with current (possibly filtered) data
    balanceChart = new Chart(document.getElementById('balanceChart'), {
      type: 'bar',
      data: {
        labels: balances.map(b => b._id),
        datasets: [{
            label: 'Débit',
            data: balances.map(b => b.debit),
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
          },
          {
            label: 'Crédit',
            data: balances.map(b => b.credit),
            backgroundColor: 'rgba(255, 99, 132, 0.7)'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        }
      }
    });
    const months = {};
    journal.forEach(e => {
      if (!e.date) return;
      const m = e.date.substring(0, 7);
      if (!months[m]) months[m] = {
        debit: 0,
        credit: 0
      };
      months[m].debit += e.debit || 0;
      months[m].credit += e.credit || 0;
    });
    const monthLabels = Object.keys(months);
    const monthDebits = monthLabels.map(m => months[m].debit);
    const monthCredits = monthLabels.map(m => months[m].credit);
    journalChart = new Chart(document.getElementById('journalChart'), {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [{
            label: 'Débit',
            data: monthDebits,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true
          },
          {
            label: 'Crédit',
            data: monthCredits,
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        }
      }
    });

    // --- Attach event listeners for real-time filter updates ---
    document.getElementById('filterStart').addEventListener('change', updateDashboard);
    document.getElementById('filterEnd').addEventListener('change', updateDashboard);
    
    // Populate details table and refresh charts to reflect current filter inputs
    updateDashboard();

    // Attach AccountSearch to filterCompte input (search suggestions)
    (function() {
      var hidden = document.getElementById('filterCompte_code');
      AccountSearch.fetchComptes().then(function() {
        // prefill display if code exists
        var initial = hidden ? hidden.value : '';
        if (initial) {
          var found = (window.comptesList || []).find(c => c.code === initial);
          if (found) document.getElementById('filterCompte').value = found.code + ' — ' + (found.label || '');
        }

        AccountSearch.createSuggestionBox({
          inputId: 'filterCompte',
          suggestionsId: 'filter_compte_suggestions',
          renderItemHtml: function(c) {
            return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
          },
          onChoose: function(item) {
            if (!item) return;
            document.getElementById('filterCompte').value = item.code + ' — ' + (item.label || '');
            if (hidden) hidden.value = item.code;
            updateDashboard();
          }
        });

        // if user clears the display, clear the code and trigger update
        document.getElementById('filterCompte').addEventListener('input', function() {
          if (!this.value && hidden) {
            hidden.value = '';
            updateDashboard();
          }
        });
      }).catch(console.error);
    })();
  });
  </script>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>