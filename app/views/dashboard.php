<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
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
          <div>
            <button class="btn btn-danger me-2" onclick="exportPDF()">Exporter PDF</button>
            <a class="btn btn-outline-secondary"
              href="?page=dashboard&action=export&format=pdf&compte=<?= htmlspecialchars($_GET['compte'] ?? '') ?>&date_debut=<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>&date_fin=<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">Exporter
              PDF (serveur)</a>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered" id="detailTable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Compte</th>
                <th>Libellé</th>
                <th>Débit</th>
                <th>Crédit</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
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

    window.addEventListener('error', function (e) {
      console.error('❌ JS Error:', e.message, 'at', e.filename + ':' + e.lineno);
    });

    window.addEventListener('unhandledrejection', function (e) {
      console.error('❌ Promise Rejection:', e.reason);
    });

    function updateDashboard() {
      const codeEl = document.getElementById('filterCompte_code');
      const compte = codeEl ? codeEl.value : document.getElementById('filterCompte').value;
      const start = document.getElementById('filterStart').value;
      const end = document.getElementById('filterEnd').value;
      // Filtre journal
      let filteredJournal = journal.filter(e => {
        if (compte && e.compte !== compte) return false;
        if (start && (!e.date || e.date.substring(0, 7) < start)) return false;
        if (end && (!e.date || e.date.substring(0, 7) > end)) return false;
        return true;
      });
      filteredJournalGlobal = filteredJournal;
      // Filtre balances
      let filteredBalances = balances.filter(b => !compte || b._id === compte);
      // Balance chart
      const balanceLabels = filteredBalances.map(b => b._id);
      const balanceDebits = filteredBalances.map(b => b.debit);
      const balanceCredits = filteredBalances.map(b => b.credit);
      balanceChart.data.labels = balanceLabels;
      balanceChart.data.datasets[0].data = balanceDebits;
      balanceChart.data.datasets[1].data = balanceCredits;
      balanceChart.update();
      // Journal chart
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
      const monthLabels = Object.keys(months);
      const monthDebits = monthLabels.map(m => months[m].debit);
      const monthCredits = monthLabels.map(m => months[m].credit);
      journalChart.data.labels = monthLabels;
      journalChart.data.datasets[0].data = monthDebits;
      journalChart.data.datasets[1].data = monthCredits;
      journalChart.update();
      // Affichage détaillé
      const tbody = document.querySelector('#detailTable tbody');
      tbody.innerHTML = '';
      filteredJournal.forEach(e => {
        const tr = document.createElement('tr');
        tr.innerHTML =
          `<td>${e.date || ''}</td><td>${e.compte || ''}</td><td>${e.libelle || ''}</td><td>${e.debit || ''}</td><td>${e.credit || ''}</td>`;
        tbody.appendChild(tr);
      });
    }

    function buildReportHeaderHTML(titleText) {
      const logoUrl = window.location.origin + '/asset.php?f=images/logo.png';
      const now = new Date();
      const dateStr = now.toLocaleString();
      return `
      <div style="display:flex;align-items:flex-start;gap:12px;padding:8px 0;">
        <div style="flex:0 0 auto;text-align:left;">
          <img src="${logoUrl}" style="height:70px;" alt="Logo">
          <div style="font-size:12px;margin-top:6px;line-height:1.2;">N° RCCM : CD/KNG/RCCM/24-B-D4138<br>ID-NAT : 01-F4200-N 37015G<br>N° IMPOT : A2504347D<br>N° d’affiliation INSS : 1022461300<br>N° d’immatriculation A L’INPP : A2504347D</div>
        </div>
        <div style="flex:1 1 auto;text-align:right;font-size:12px;color:#333;">${dateStr}</div>
      </div>
      <div style="height:4px;background:#0d6efd;margin:8px 0 12px 0"></div>
      <h2 style="text-align:center;font-weight:700;margin:6px 0 12px 0">${titleText}</h2>
    `;
    }

    function exportPDF() {
      const title = 'Export Journal Comptable';
      let html = '<html><head><meta charset="utf-8"><style>table{width:100%;border-collapse:collapse}th,td{border:1px solid #333;padding:6px;text-align:center} td{text-align:left} </style></head><body>';
      html += buildReportHeaderHTML(title);
      html += '<table><thead><tr><th>Date</th><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr></thead><tbody>';
      filteredJournalGlobal.forEach(e => {
        html += `<tr><td>${e.date || ''}</td><td>${e.compte || ''}</td><td>${e.libelle || ''}</td><td style="text-align:right">${e.debit || ''}</td><td style="text-align:right">${e.credit || ''}</td></tr>`;
      });
      html += '</tbody></table></body></html>';

      const w = window.open('', '', 'width=900,height=800');
      w.document.write(html);
      w.document.close();
      setTimeout(() => { w.print(); }, 500);
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

      // --- Logo preview handling -------------------------------------------------
      (function () {
        const previewImg = document.getElementById('logoPreviewImg');
        if (!previewImg) {
          console.log('ℹ️ Logo preview image not found in DOM (was commented out)');
          return;
        }

        function markLoaded() {
          console.log('✅ Logo loaded:', previewImg.naturalWidth + 'x' + previewImg.naturalHeight);
          try {
            showTempAlert('Logo local accessible — prêt pour export PDF', 'success', 3000);
          } catch (e) {
            console.warn('Alert fn not ready:', e);
          }
        }

        function markError() {
          console.warn('⚠️ Logo failed to load');
          try {
            showTempAlert("Logo local inaccessible. L'export utilisera une image de secours.", 'warning', 5000);
          } catch (e) {
            console.warn('Alert fn not ready:', e);
          }
        }

        previewImg.addEventListener('load', markLoaded);
        previewImg.addEventListener('error', markError);

        // Forcer la vérification si l'image est déjà en cache
        if (previewImg.complete) {
          if (previewImg.naturalWidth) markLoaded();
          else markError();
        }
      })();
      // ---------------------------------------------------------------------------
      // Populate details table and refresh charts to reflect current filter inputs
      updateDashboard();

      // Attach AccountSearch to filterCompte input (search suggestions)
      (function () {
        var hidden = document.getElementById('filterCompte_code');
        AccountSearch.fetchComptes().then(function () {
          // prefill display if code exists
          var initial = hidden ? hidden.value : '';
          if (initial) {
            var found = (window.comptesList || []).find(c => c.code === initial);
            if (found) document.getElementById('filterCompte').value = found.code + ' — ' + (found.label || '');
          }

          AccountSearch.createSuggestionBox({
            inputId: 'filterCompte',
            suggestionsId: 'filter_compte_suggestions',
            renderItemHtml: function (c) { return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`; },
            onChoose: function (item) { if (!item) return; document.getElementById('filterCompte').value = item.code + ' — ' + (item.label || ''); if (hidden) hidden.value = item.code; updateDashboard(); }
          });

          // if user clears the display, clear the code
          document.getElementById('filterCompte').addEventListener('input', function () { if (!this.value && hidden) hidden.value = ''; });
        }).catch(console.error);
      })();
    });
  </script>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>