<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Dashboard - Comptabilité</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
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
        <input type="text" id="filterCompte" name="compte" class="form-control"
          value="<?= htmlspecialchars($_GET['compte'] ?? '') ?>" placeholder="Ex: 601">
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
          <button class="btn btn-success me-2" onclick="exportCSV()">Exporter CSV</button>
          <button class="btn btn-primary me-2" onclick="exportWord()">Exporter Word</button>
          <button class="btn btn-danger" onclick="exportPDF()">Exporter PDF</button>
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
    const balances = <?= json_encode($balances) ?>;
    const journal = <?= json_encode($journal) ?>;
    let balanceChart, journalChart, filteredJournalGlobal = [];

    function updateDashboard() {
      const compte = document.getElementById('filterCompte').value;
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

    function exportCSV() {
      let rows = [
        ['Date', 'Compte', 'Libellé', 'Débit', 'Crédit']
      ];
      filteredJournalGlobal.forEach(e => {
        rows.push([e.date || '', e.compte || '', e.libelle || '', e.debit || '', e.credit || '']);
      });
      let csv = rows.map(r => r.map(x => '"' + String(x).replace(/"/g, '""') + '"').join(',')).join('\r\n');
      let blob = new Blob([csv], {
        type: 'text/csv'
      });
      let a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'journal_export.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }

    function exportWord() {
      let logo =
        '<img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/Logo_Compta.png" alt="Logo" style="height:50px;margin-bottom:10px;">';
      let title = '<h2 style="margin-bottom:10px;">Export Journal Comptable</h2>';
      let html = logo + title +
        '<table border="1"><tr><th>Date</th><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr>';
      filteredJournalGlobal.forEach(e => {
        html +=
          `<tr><td>${e.date || ''}</td><td>${e.compte || ''}</td><td>${e.libelle || ''}</td><td>${e.debit || ''}</td><td>${e.credit || ''}</td></tr>`;
      });
      html += '</table>';
      let blob = new Blob([
        '<html><head><meta charset="utf-8"></head><body>' + html + '</body></html>'
      ], {
        type: 'application/msword'
      });
      let a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'journal_export.doc';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }

    function exportPDF() {
      let win = window.open('', '', 'width=800,height=600');
      let html = '<html><head><title>Export PDF</title><meta charset="utf-8">';
      html +=
        '<style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #333;padding:4px;text-align:center} img{height:50px;margin-bottom:10px;}</style>';
      html += '</head><body>';
      html += '<img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/Logo_Compta.png" alt="Logo">';
      html += '<h2 style="margin-bottom:10px;">Export Journal Comptable</h2>';
      html += '<table><tr><th>Date</th><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr>';
      filteredJournalGlobal.forEach(e => {
        html +=
          `<tr><td>${e.date || ''}</td><td>${e.compte || ''}</td><td>${e.libelle || ''}</td><td>${e.debit || ''}</td><td>${e.credit || ''}</td></tr>`;
      });
      html += '</table></body></html>';
      win.document.write(html);
      win.document.close();
      win.print();
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
      // Populate details table and refresh charts to reflect current filter inputs
      updateDashboard();
    });
  </script>
</body>

</html>