<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Fiche de Stock</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Fiche de Stock</h2>
      <button class="btn btn-primary"
        style="position: absolute; right:2%; bottom: 2%; font-weight: bold; font-size: large" id="btnNouveau"
        data-bs-toggle="modal" data-bs-target="#stockModal">+</button>
    </div>

    <!-- Bouton Nouveau flottant (optionnel) -->

    <!-- Filtres -->

    <form class="d-flex gap-2" method="get" action="">
      <input type="hidden" name="page" value="stock">
      <input type="date" name="date_debut" class="form-control form-control-sm"
        value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
      <input type="date" name="date_fin" class="form-control form-control-sm"
        value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
      <input type="text" name="compte_filtre" class="form-control form-control-sm"
        value="<?= htmlspecialchars($_GET['compte_filtre'] ?? '') ?>" placeholder="Compte">
      <input type="text" name="lieu" class="form-control form-control-sm"
        value="<?= htmlspecialchars($_GET['lieu'] ?? '') ?>" placeholder="Lieu">
      <button type="submit" class="btn btn-sm btn-secondary">Filtrer</button>
      <a class="btn btn-sm btn-outline-secondary" href="?page=stock">Afficher tout</a>
    </form>

    <div class="d-flex mt-3 mb-1" style="margin-left:90%">
      <button class="btn btn-sm btn-success" onclick="exportStockPDF()">Exporter PDF</button>
    </div>

    <!-- Modal d'ajout -->
    <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
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
                  <input type="text" name="compte" id="compte" class="form-control" required>
                </div>
                <div class="col-6">
                  <label class="form-label">Intitulé compte</label>
                  <input type="text" name="intitule" id="intitule" class="form-control" required>
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
      <table class="table table-sm table-bordered" style="font-size:0.9rem;">
        <thead>
          <tr>
            <th>Date</th>
            <th>Compte</th>
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
            <td><?= $compte ?></td>
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
                <a href="?page=stock&action=edit&id=<?= urlencode($id) ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="?page=stock&action=delete&id=<?= urlencode($id) ?>" class="btn btn-sm btn-danger"
                  onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
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

  <script>
  <?php
    // Embed logo image as base64 so it works under public docroot
    $logoPath = __DIR__ . '/log.jpg';
    $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
    ?>
  // comptesMap envoyé par le controller (comptesMap)
  var comptesIntitules =
    <?= json_encode($comptesMap ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

  document.addEventListener('DOMContentLoaded', function() {
    var compteInput = document.getElementById('compte');
    var intituleInput = document.getElementById('intitule');
    var operationSelect = document.getElementById('operation');
    var puInput = document.getElementById('pu');
    var puWrapper = document.getElementById('puWrapper');

    if (compteInput && intituleInput) {
      compteInput.addEventListener('blur', function() {
        var val = compteInput.value.trim();
        if (val && comptesIntitules[val]) {
          intituleInput.value = comptesIntitules[val];
          intituleInput.readOnly = true;
        } else {
          intituleInput.value = '';
          intituleInput.readOnly = false;
        }
      });
    }

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
  });
  </script>

  <script>
  function exportStockPDF() {
    let win = window.open('', '', 'width=800,height=600');
    let html = '<html><head><title>Fiche de Stock</title><meta charset="utf-8">';
    html +=
      '<style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #333;padding:4px;text-align:center} h2{margin:0 0 10px 0}</style>';
    html += '</head><body>';
    <?php if (!empty($logoData)) { ?>
    html +=
      '<div style="text-align:center;margin-bottom:6px;"><img style="max-height:70px;" src="data:image/jpeg;base64,<?= $logoData ?>" alt="Logo"></div>';
    <?php } ?>
    html += '<h2 style="text-align:center">Fiche de Stock</h2>';
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
    html += table.outerHTML;
    html += '</body></html>';
    win.document.write(html);
    win.document.close();
    win.print();
  }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>