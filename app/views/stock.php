<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Fiche de Stock';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body style="position: relative;">
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container-fluid mt-4">
    <style>
      .stock-table {
        font-size: 0.82rem;
      }

      .fab-stock {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        z-index: 1050;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
      }

      @media (max-width: 575px) {
        .fab-stock {
          right: 0.5rem;
          bottom: 0.5rem;
        }
      }

      /* Compact filters */
      .card-body.py-2 .form-label.small {
        margin-bottom: 0.2rem;
      }

      .card-body.py-2 .form-control-sm {
        padding: .25rem .5rem;
      }

      #compte_filtre_suggestions .list-group-item,
      #compte_suggestions .list-group-item {
        cursor: pointer;
      }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <button class="btn btn-primary d-none d-md-inline-flex" id="btnNouveauHeader" data-bs-toggle="modal"
          data-bs-target="#stockModal">
          Nouvel enregistrement
        </button>
      </div>
    </div>

    <!-- Filters card (compact, with icons) -->
    <div class="no-print">
      <div class="card mb-3">
        <div class="card-body py-2">
          <form id="stockFilterForm" class="row g-2 align-items-end" method="get" action="">
            <input type="hidden" name="page" value="stock">

            <div class="col-auto">
              <label class="form-label small">📅 Début</label>
              <input type="date" name="date_debut" class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
            </div>

            <div class="col-auto">
              <label class="form-label small">📅 Fin</label>
              <input type="date" name="date_fin" class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
            </div>

            <div class="col-md-3 col-auto position-relative">
              <label class="form-label small">🔎 Compte</label>
              <input type="text" id="compte_filtre_display" class="form-control form-control-sm"
                placeholder="Code ou libellé">
              <input type="hidden" id="compte_filtre" name="compte_filtre"
                value="<?= htmlspecialchars($_GET['compte_filtre'] ?? '') ?>">
              <div id="compte_filtre_suggestions" class="list-group"
                style="position:absolute;z-index:1060;width:100%;max-height:240px;overflow:auto;display:none;"></div>
            </div>

            <div class="col-md-3 col-auto">
              <label class="form-label small">🏷️ Lieu</label>
              <input type="text" name="lieu" class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['lieu'] ?? '') ?>" placeholder="Lieu">
            </div>

            <div class="col-auto ms-auto d-flex gap-2">
              <button type="submit" class="btn btn-sm btn-secondary">🔁 Filtrer</button>
              <button type="button" id="clearStockFilters" class="btn btn-sm btn-outline-secondary">✖ Effacer</button>
              <a class="btn btn-sm btn-outline-secondary" href="?page=stock">Afficher tout</a>
              <button class="btn btn-sm btn-success" type="button" onclick="exportStockPDF()">🧾 Exporter</button>
            </div>
          </form>
        </div>
      </div>
    </div>



    <!-- Modal d'ajout -->
    <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <form method="post" action="?page=stock&action=add" id="stockForm" onsubmit="return validateStockForm();">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            <div class="modal-header">
              <h5 class="modal-title" id="stockModalLabel">Nouvelle écriture de stock</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label">Date</label>
                  <input type="date" name="date" class="form-control" required>
                </div>
                <div class="col-6">
                  <label class="form-label">Opération</label>
                  <select name="operation" id="operation" class="form-select" required>
                    <option value="entree">Entrée</option>
                    <option value="sortie">Sortie</option>
                  </select>
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label">Compte</label>
                  <div class="position-relative">
                    <input type="text" id="compte_search" class="form-control"
                      placeholder="Rechercher un compte (code ou libellé)" required>
                    <div id="compte_suggestions" class="list-group"
                      style="position:absolute;z-index:1060;width:100%;max-height:240px;overflow:auto;display:none;">
                    </div>
                  </div>
                  <input type="hidden" name="compte" id="compte">
                  <input type="text" id="compte_display" class="form-control mt-2" placeholder="Compte sélectionné"
                    readonly>
                </div>
                <div class="col-6">
                  <label class="form-label">Intitulé compte</label>
                  <input type="text" name="intitule" id="intitule" class="form-control" required readonly>
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label">Lieu d'opération</label>
                  <input type="text" name="lieu" id="lieu" class="form-control" required>
                </div>
                <div class="col-6">
                  <label class="form-label">Désignation</label>
                  <input type="text" name="designation" class="form-control" required>
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label">Quantité</label>
                  <input type="number" step="0.01" name="quantite" id="quantite" class="form-control" required>
                </div>
                <div class="col-6" id="puWrapper">
                  <label class="form-label">Prix Unitaire</label>
                  <input type="number" step="0.01" name="pu" id="pu" class="form-control">
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-12 d-flex align-items-end">
                  <button type="submit" class="btn btn-success w-100">Ajouter</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="table-responsive">
      <h2 class="fw-bold">Fiche de stock</h2>
      <table class="table table-sm table-bordered table-striped table-hover stock-table">
        <thead>
          <tr>
            <th>Date</th>
            <!-- <th>Compte</th> -->
            <th>Lieu</th>
            <th>Intitulé</th>
            <th>Désignation</th>
            <th class="text-end">Entrée Qte</th>
            <th class="text-end">Entrée PU</th>
            <th class="text-end">Entrée Total</th>
            <th class="text-end">Sortie Qte</th>
            <th class="text-end">Stock Qte</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (!empty($stocks) && is_array($stocks)):
            foreach ($stocks as $s):
              // extraire id lisible
              $id = '';
              if (isset($s['_id'])) {
                if (is_object($s['_id']) && property_exists($s['_id'], '$oid'))
                  $id = $s['_id']->{'$oid'};
                elseif (is_array($s['_id']) && isset($s['_id']['$oid']))
                  $id = $s['_id']['$oid'];
                else
                  $id = (string) $s['_id'];
              }

              $date = htmlspecialchars($s['date'] ?? '');
              $compte = htmlspecialchars($s['compte'] ?? '');
              $intitule = htmlspecialchars($s['intitule'] ?? '');
              $lieu = htmlspecialchars($s['lieu'] ?? '');
              $designation = htmlspecialchars($s['designation'] ?? '');
              $entreeQ = isset($s['entree']['qte']) ? number_format((float) $s['entree']['qte'], 2) : '';
              $entreePU = isset($s['entree']['pu']) ? ('$' . number_format((float) $s['entree']['pu'], 2)) : '';
              $entreeTotal = isset($s['entree']['total']) ? ('$' . number_format((float) $s['entree']['total'], 2)) : '';
              $sortieQ = isset($s['sortie']['qte']) ? number_format((float) $s['sortie']['qte'], 2) : '';
              $stockQ = isset($s['stock']['qte']) ? number_format((float) $s['stock']['qte'], 2) : '';
              ?>
              <tr>
                <td><?= $date ?></td>
                <!-- <td><?php //$compte ?></td> -->
                <td><?= $lieu ?></td>
                <td><?= $intitule ?></td>
                <td><?= $designation ?></td>
                <td class="text-end"><?= $entreeQ ?></td>
                <td class="text-end"><?= $entreePU ?></td>
                <td class="text-end"><?= $entreeTotal ?></td>
                <td class="text-end"><?= $sortieQ ?></td>
                <td class="text-end"><?= $stockQ ?></td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-2">
                    <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['accountant', 'admin'])): ?>
                      <a href="?page=stock&action=edit&id=<?= urlencode($id) ?>" class="btn btn-sm btn-warning">Modifier</a>
                      <a href="?page=stock&action=delete&id=<?= urlencode($id) ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php
            endforeach;
          else:
            echo '<tr><td colspan="10" class="text-center">Aucune opération trouvée.</td></tr>';
          endif;
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="fab-stock">
    <button class="btn btn-primary fab-stock" id="fabStock" data-bs-toggle="modal" data-bs-target="#stockModal"
      aria-label="Nouvel enregistrement">+</button>
  </div>
  <script>
    <?php
    // Embed logo image as base64 so it works under public docroot
    $logoPath = __DIR__ . '/log.jpg';
    $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    ?>

    // Initialize account search & fallback loader
    async function loadComptes() {
      try {
        const response = await fetch('?page=api&action=comptes');
        const comptes = await response.json();
        window.comptesList = comptes || [];
      } catch (error) {
        console.error('Erreur lors du chargement des comptes:', error);
        window.comptesList = [];
      }
    }

    function updateIntitule() {
      // If a select exists (legacy), use it
      const select = document.getElementById('compte');
      const intituleInput = document.getElementById('intitule');
      if (select && select.tagName && select.tagName.toLowerCase() === 'select') {
        const selected = select.options[select.selectedIndex];
        if (selected && selected.value && selected.dataset.intitule) {
          intituleInput.value = selected.dataset.intitule;
          return;
        }
      }
      // Otherwise lookup from loaded comptes
      const code = document.getElementById('compte') ? document.getElementById('compte').value : '';
      if (code) {
        const acc = (window.comptesList || []).find(c => c.code === code);
        intituleInput.value = acc ? (acc.intitule || '') : '';
      } else {
        intituleInput.value = '';
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      // load comptes into window list
      loadComptes().then(function () {
        // attach AccountSearch for filter 'Compte'
        AccountSearch.createSuggestionBox({
          inputId: 'compte_filtre_display',
          suggestionsId: 'compte_filtre_suggestions',
          renderItemHtml: function (c) {
            return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
          },
          onChoose: function (item) {
            if (!item) return;
            document.getElementById('compte_filtre_display').value = item.code + ' — ' + (item.label || '');
            document.getElementById('compte_filtre').value = item.code;
          }
        });

        // attach AccountSearch for modal 'compte'
        AccountSearch.createSuggestionBox({
          inputId: 'compte_search',
          suggestionsId: 'compte_suggestions',
          renderItemHtml: function (c) {
            return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
          },
          onChoose: function (item) {
            if (!item) return;
            document.getElementById('compte').value = item.code;
            document.getElementById('compte_display').value = item.code + ' — ' + (item.label || '');
            document.getElementById('intitule').value = item.intitule || '';
          }
        });

        // prefill filter display if value present
        var initial = document.getElementById('compte_filtre') ? document.getElementById('compte_filtre').value :
          '';
        if (initial) {
          var found = (window.comptesList || []).find(c => c.code === initial);
          if (found) document.getElementById('compte_filtre_display').value = found.code + ' — ' + (found.label ||
            '');
        }
      }).catch(console.error);
    });

    document.addEventListener('DOMContentLoaded', function () {
      // Charger les comptes
      loadComptes();

      var operationSelect = document.getElementById('operation');
      var puInput = document.getElementById('pu');
      var puWrapper = document.getElementById('puWrapper');

      function togglePU() {
        if (!operationSelect || !puWrapper || !puInput) return;
        if (operationSelect.value === 'sortie') {
          puWrapper.style.display = 'none';
          puInput.required = false;
        } else {
          puWrapper.style.display = '';
          puInput.required = true;
        }
      }
      if (operationSelect) {
        operationSelect.addEventListener('change', togglePU);
        togglePU();
      }

      // Live PT calculation display (optional)
      const quantiteInput = document.getElementById('quantite');
      const puDisplay = document.getElementById('pu');

      function validateStockForm() {
        const op = operationSelect.value;
        const q = parseFloat(quantiteInput.value || '0');
        if (!q || q <= 0) {
          alert('Quantité invalide');
          return false;
        }
        if (op === 'entree') {
          const puVal = parseFloat(puDisplay.value || '0');
          if (!puVal || puVal <= 0) {
            alert('PU requis pour une entrée');
            return false;
          }
        }
        return true;
      }

      // Clear filters button (already in form)
      var clearBtn = document.getElementById('clearStockFilters');
      if (clearBtn) {
        clearBtn.addEventListener('click', function () {
          var dstart = document.querySelector('input[name="date_debut"]');
          if (dstart) dstart.value = '';
          var dend = document.querySelector('input[name="date_fin"]');
          if (dend) dend.value = '';
          var lieu = document.querySelector('input[name="lieu"]');
          if (lieu) lieu.value = '';
          var cfDisplay = document.getElementById('compte_filtre_display');
          if (cfDisplay) cfDisplay.value = '';
          var cfHidden = document.getElementById('compte_filtre');
          if (cfHidden) cfHidden.value = '';
        });
      }

      // Reset modal form on close
      var stockModalEl = document.getElementById('stockModal');
      if (stockModalEl) {
        stockModalEl.addEventListener('hidden.bs.modal', function () {
          var form = document.getElementById('stockForm');
          if (form) form.reset();
          var intitule = document.getElementById('intitule');
          if (intitule) intitule.value = '';
          var hiddenCompte = document.getElementById('compte');
          if (hiddenCompte) hiddenCompte.value = '';
          var displayCompte = document.getElementById('compte_display');
          if (displayCompte) displayCompte.value = '';
          if (typeof togglePU === 'function') togglePU();
        });
      }

      var fab = document.getElementById('fabStock');
      if (fab) fab.addEventListener('click', function () {
        var btn = document.getElementById('btnNouveauHeader');
        if (btn) btn.click();
      });
    });
  </script>

  <script>
    function exportStockPDF() {
      const logoUrl = window.location.origin + '/asset.php?f=images/logo.png';
      const now = new Date();
      const dateStr = now.toLocaleString();
      let win = window.open('', '', 'width=1000,height=900');
      let html = '<html><head><title>Fiche de Stock</title><meta charset="utf-8">';
      html +=
        '<style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #333;padding:6px;text-align:center} h2{margin:0 0 10px 0;font-weight:700;text-align:center} .header-left{float:left} .header-right{float:right;font-size:12px}</style>';
      html += '</head><body>';
      html +=
        `<div style="display:flex;align-items:flex-start;gap:12px;padding:8px 0;"><div style="flex:0 0 auto;text-align:left;"><img src="${logoUrl}" style="height:70px;" alt="Logo"><div style="font-size:12px;margin-top:6px;line-height:1.2;">N° RCCM : CD/KNG/RCCM/24-B-D4138<br>ID-NAT : 01-F4200-N 37015G<br>N° IMPOT : A2504347D<br>N° d’affiliation INSS : 1022461300<br>N° d’immatriculation A L’INPP : A2504347D</div></div><div style="flex:1 1 auto;text-align:right;font-size:12px;color:#333;">${dateStr}</div></div>`;
      html += '<div style="height:4px;background:#0d6efd;margin:8px 0 12px 0"></div>';
      html += '<h2>Fiche de Stock</h2>';
      // clone table and remove last actions column
      const table = document.querySelector('.table-responsive table').cloneNode(true);
      // remove the last header and each last cell (Actions)
      const theadRow = table.tHead && table.tHead.rows[0];
      if (theadRow && theadRow.lastElementChild) theadRow.removeChild(theadRow.lastElementChild);
      Array.from(table.tBodies[0].rows).forEach(r => {
        if (r.lastElementChild) r.removeChild(r.lastElementChild);
      });
      // also remove the "Intitulé" column (4th column index 3)
      if (theadRow && theadRow.children.length > 3) theadRow.removeChild(theadRow.children[3]);
      Array.from(table.tBodies[0].rows).forEach(r => {
        if (r.children.length > 3) r.removeChild(r.children[3]);
      });
      html += '<div style="margin-top:12px">' + table.outerHTML + '</div>';
      html += '</body></html>';
      win.document.write(html);
      win.document.close();
      setTimeout(() => {
        win.print();
      }, 300);
    }
  </script>

  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>