<?php
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
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
    <h4 class="fw-bold" style="color: blue;">Fiche de stock</h4>
    <!-- Filters card (compact, with icons) -->
    <div class="no-print">
      <div class="card mb-3">
        <div class="card-body py-2">
          <form id="stockFilterForm" class="row g-2 align-items-end justify-content-center" method="get" action="">
            <input type="hidden" name="page" value="stock">

            <div class="col-auto">
              <input type="date" name="date_debut" class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
            </div>

            <div class="col-auto">
              <input type="date" name="date_fin" class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
            </div>

            <div class="col-md-3 col-auto position-relative">
              <input type="text" id="compte_filtre_display" class="form-control form-control-sm"
                placeholder="Code ou libellé">
              <input type="hidden" id="compte_filtre" name="compte_filtre"
                value="<?= htmlspecialchars($_GET['compte_filtre'] ?? '') ?>">
              <div id="compte_filtre_suggestions" class="list-group"
                style="position:absolute;z-index:1060;width:100%;max-height:240px;overflow:auto;display:none;"></div>
            </div>

            <div class="col-auto col-auto">
              <input type="text" name="lieu" class="form-control form-control-sm"
                value="<?= htmlspecialchars($_GET['lieu'] ?? '') ?>" placeholder="Lieu">
            </div>

            <div class="col-md-4 ms-auto d-flex gap-2">
              <button type="submit" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-repeat"></i> Filtrer</button>
              <button type="button" id="clearStockFilters" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Effacer</button>
              <a class="btn btn-sm btn-outline-secondary" href="?page=stock">Afficher tout</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Export PDF Button - Desktop and Mobile -->
    <div class="no-print">
      <!-- Desktop version -->
      <a class="btn btn-export-pdf d-none d-md-inline-flex" 
        href="?page=stock&action=export&format=pdf&<?= http_build_query(['date_debut' => $_GET['date_debut'] ?? '', 'date_fin' => $_GET['date_fin'] ?? '', 'compte_filtre' => $_GET['compte_filtre'] ?? '', 'lieu' => $_GET['lieu'] ?? '']) ?>"
        style="position: fixed; bottom: 80px; right: 2%; z-index: 1070;">
        <i class="bi bi-file-earmark-pdf me-2"></i> Exporter PDF
      </a>
      <!-- Mobile version -->
      <a class="btn btn-export-pdf-mobile d-md-none" 
        href="?page=stock&action=export&format=pdf&<?= http_build_query(['date_debut' => $_GET['date_debut'] ?? '', 'date_fin' => $_GET['date_fin'] ?? '', 'compte_filtre' => $_GET['compte_filtre'] ?? '', 'lieu' => $_GET['lieu'] ?? '']) ?>"
        style="position: fixed; bottom: 80px; right: 16px; z-index: 1070;"
        title="Exporter PDF">
        <i class="bi bi-file-earmark-pdf"></i>
      </a>
    </div>

    <!-- Modal d'ajout -->
    <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <form method="post" action="?page=stock&action=add" id="stockForm">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            <div class="modal-header">
              <h5 class="modal-title" id="stockModalLabel">Nouvelle écriture de stock</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <!-- Zone d'alertes pour messages d'erreur/succès -->
              <div id="stockModalAlert" style="display:none; margin-bottom: 1rem;">
                <div id="stockAlertContent"></div>
              </div>

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
                      placeholder="Rechercher un compte (code ou libellé)">
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
                <div class="col-12 d-flex align-items-end gap-2">
                  <button type="submit" class="btn btn-success flex-fill" id="stockSubmitBtn">Ajouter</button>
                  <button type="button" class="btn btn-secondary flex-fill" id="stockReloadBtn" style="display:none;">Recharger</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="table-responsive">
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
                    <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['accountant', 'admin', 'stock_manager'])): ?>
                      <a href="?page=stock&action=edit&id=<?= urlencode($id) ?>" class="btn btn-sm btn-warning">Modifier</a>
                      <?php $csrfToken = \App\Core\Csrf::getToken(); ?>
                      <a href="?page=stock&action=delete&id=<?= urlencode($id) ?>&token=<?= urlencode($csrfToken) ?>" class="btn btn-sm btn-danger"
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

    <!-- Pagination Section -->
    <?php if (isset($pagination) && $pagination->getTotalPages() > 1): ?>
      <nav aria-label="Pagination" class="mt-4 mb-4">
        <ul class="pagination justify-content-center">
          <!-- Texte informatif -->
          <li class="page-item disabled">
            <span class="page-link"><?= htmlspecialchars($pagination->getDisplayMessage()) ?></span>
          </li>

          <!-- Bouton Précédent -->
          <?php if ($pagination->hasPreviousPage()): ?>
            <li class="page-item">
              <a class="page-link"
                href="?page=stock&page_num=1&date_debut=<?= urlencode($_GET['date_debut'] ?? '') ?>&date_fin=<?= urlencode($_GET['date_fin'] ?? '') ?>&compte_filtre=<?= urlencode($_GET['compte_filtre'] ?? '') ?>&lieu=<?= urlencode($_GET['lieu'] ?? '') ?>"
                aria-label="Première">
                <span aria-hidden="true">&laquo;&laquo;</span>
              </a>
            </li>
            <li class="page-item">
              <a class="page-link"
                href="?page=stock&page_num=<?= $pagination->getPreviousPage() ?>&date_debut=<?= urlencode($_GET['date_debut'] ?? '') ?>&date_fin=<?= urlencode($_GET['date_fin'] ?? '') ?>&compte_filtre=<?= urlencode($_GET['compte_filtre'] ?? '') ?>&lieu=<?= urlencode($_GET['lieu'] ?? '') ?>"
                aria-label="Précédent">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>
          <?php else: ?>
            <li class="page-item disabled">
              <span class="page-link">&laquo;&laquo;</span>
            </li>
            <li class="page-item disabled">
              <span class="page-link">&laquo;</span>
            </li>
          <?php endif; ?>

          <!-- Numéros de pages -->
          <?php foreach ($pagination->getPageNumbers(2) as $pageNum): ?>
            <?php if ($pageNum === '...'): ?>
              <li class="page-item disabled">
                <span class="page-link">...</span>
              </li>
            <?php elseif ($pageNum == $pagination->getCurrentPage()): ?>
              <li class="page-item active">
                <span class="page-link"><?= $pageNum ?></span>
              </li>
            <?php else: ?>
              <li class="page-item">
                <a class="page-link"
                  href="?page=stock&page_num=<?= $pageNum ?>&date_debut=<?= urlencode($_GET['date_debut'] ?? '') ?>&date_fin=<?= urlencode($_GET['date_fin'] ?? '') ?>&compte_filtre=<?= urlencode($_GET['compte_filtre'] ?? '') ?>&lieu=<?= urlencode($_GET['lieu'] ?? '') ?>"><?= $pageNum ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>

          <!-- Bouton Suivant -->
          <?php if ($pagination->hasNextPage()): ?>
            <li class="page-item">
              <a class="page-link"
                href="?page=stock&page_num=<?= $pagination->getNextPage() ?>&date_debut=<?= urlencode($_GET['date_debut'] ?? '') ?>&date_fin=<?= urlencode($_GET['date_fin'] ?? '') ?>&compte_filtre=<?= urlencode($_GET['compte_filtre'] ?? '') ?>&lieu=<?= urlencode($_GET['lieu'] ?? '') ?>"
                aria-label="Suivant">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
            <li class="page-item">
              <a class="page-link"
                href="?page=stock&page_num=<?= $pagination->getTotalPages() ?>&date_debut=<?= urlencode($_GET['date_debut'] ?? '') ?>&date_fin=<?= urlencode($_GET['date_fin'] ?? '') ?>&compte_filtre=<?= urlencode($_GET['compte_filtre'] ?? '') ?>&lieu=<?= urlencode($_GET['lieu'] ?? '') ?>"
                aria-label="Dernière">
                <span aria-hidden="true">&raquo;&raquo;</span>
              </a>
            </li>
          <?php else: ?>
            <li class="page-item disabled">
              <span class="page-link">&raquo;</span>
            </li>
            <li class="page-item disabled">
              <span class="page-link">&raquo;&raquo;</span>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>

    <div class="no-print">
      <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['accountant', 'admin', 'stock_manager'])): ?>
        <!-- Version desktop -->
        <button class="btn btn-primary d-none d-md-inline-flex" id="fabStockDesktop" data-bs-toggle="modal" data-bs-target="#stockModal"
          title="Ajouter une écriture de stock"
          style="font-weight: bold; font-size: large; position: fixed; right: 2%; bottom: 2%;">
          <i class="bi bi-plus-circle me-2"></i> Nouvelle opération
        </button>
        <!-- Version mobile FAB -->
        <button class="btn btn-primary d-md-none fab fab-stock" id="fabStock" data-bs-toggle="modal" data-bs-target="#stockModal"
          aria-label="Nouvel enregistrement"
          style="position: fixed; right: 16px; bottom: 16px; width: 56px; height: 56px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
          <i class="bi bi-plus-lg" style="font-size: 24px;"></i>
        </button>
      <?php endif; ?>
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
              document.getElementById('compte_filtre_display').value = item.code + ' — ' + (item.label ||
                '');
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
          var initial = document.getElementById('compte_filtre') ? document.getElementById('compte_filtre')
            .value :
            '';
          if (initial) {
            var found = (window.comptesList || []).find(c => c.code === initial);
            if (found) document.getElementById('compte_filtre_display').value = found.code + ' — ' + (found
              .label ||
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

        // Gestion de la soumission AJAX du formulaire
        const stockForm = document.getElementById('stockForm');
        const stockModalAlert = document.getElementById('stockModalAlert');
        const stockAlertContent = document.getElementById('stockAlertContent');
        const stockSubmitBtn = document.getElementById('stockSubmitBtn');
        const stockReloadBtn = document.getElementById('stockReloadBtn');

        function showAlert(message, type = 'danger') {
          const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
          stockAlertContent.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
              ${message}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          `;
          stockModalAlert.style.display = 'block';
        }

        function hideAlert() {
          stockModalAlert.style.display = 'none';
          stockAlertContent.innerHTML = '';
        }

        if (stockForm) {
          stockForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            hideAlert();
            
            const op = document.getElementById('operation').value;
            const q = parseFloat(quantiteInput.value || '0');
            
            // Validation côté client
            if (!q || q <= 0) {
              showAlert('Quantité invalide', 'danger');
              return false;
            }
            if (op === 'entree') {
              const puVal = parseFloat(puDisplay.value || '0');
              if (!puVal || puVal <= 0) {
                showAlert('PU requis pour une entrée', 'danger');
                return false;
              }
            }

            // Soumettre via AJAX
            const formData = new FormData(stockForm);
            
            try {
              stockSubmitBtn.disabled = true;
              const response = await fetch(stockForm.action, {
                method: 'POST',
                body: formData
              });

              const data = await response.json();

              if (data.success) {
                showAlert(data.message || 'Opération enregistrée avec succès', 'success');
                // Masquer le bouton Ajouter et afficher le bouton Recharger
                stockSubmitBtn.style.display = 'none';
                stockReloadBtn.style.display = 'block';
                
                // Réinitialiser le formulaire après un court délai
                setTimeout(() => {
                  stockForm.reset();
                  document.getElementById('intitule').value = '';
                  document.getElementById('compte').value = '';
                  document.getElementById('compte_display').value = '';
                  document.getElementById('compte_search').value = '';
                  if (typeof togglePU === 'function') togglePU();
                }, 1000);
              } else {
                showAlert(data.error || 'Une erreur est survenue', 'danger');
                stockSubmitBtn.disabled = false;
              }
            } catch (error) {
              console.error('Erreur lors de la soumission:', error);
              showAlert('Erreur réseau: ' + error.message, 'danger');
              stockSubmitBtn.disabled = false;
            }
          });

          // Bouton recharger
          if (stockReloadBtn) {
            stockReloadBtn.addEventListener('click', function() {
              location.reload();
            });
          }
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