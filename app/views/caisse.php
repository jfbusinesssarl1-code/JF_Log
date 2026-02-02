<?php
if (session_status() === PHP_SESSION_NONE)
  session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Livre de caisse';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <a class="btn btn-outline-secondary me-2" href="?page=caisse&action=export&format=pdf">Exporter PDF</a>
        <?php if (!class_exists('\\Mpdf\\Mpdf')): ?>
        <button class="btn btn-outline-info" type="button" onclick="exportCaisseToPDFClient()">Exporter PDF
          (imprimer)</button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-4">
      <div class="card-header bg-light">
        <h5 class="mb-0">Filtres</h5>
      </div>
      <div class="card-body">
        <form method="get" class="row g-2">
          <input type="hidden" name="page" value="caisse">
          <div class="col-md-2">
            <input type="date" name="date_debut" class="form-control"
              value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <input type="date" name="date_fin" class="form-control"
              value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <input type="text" name="operateur" class="form-control" placeholder="Nom opérateur"
              value="<?= htmlspecialchars($filters['operateur'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <input type="text" name="numero_bon_manuscrit" class="form-control" placeholder="Numéro du bon"
              value="<?= htmlspecialchars($filters['numero_bon_manuscrit'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <select name="type" class="form-select">
              <option value="">Tous</option>
              <option value="entree" <?= ($filters['type'] ?? '') === 'entree' ? 'selected' : '' ?>>Bon d'entrée
              </option>
              <option value="sortie" <?= ($filters['type'] ?? '') === 'sortie' ? 'selected' : '' ?>>Bon de sortie
              </option>
            </select>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <a href="?page=caisse" class="btn btn-secondary w-100">Tous</a>
          </div>
        </form>
      </div>
    </div>

    <div class="table-responsive">

      <table class="table table-bordered">
        <thead>
          <h2 class="mb-0 fw-bold fs-2">Livre de caisse</h2>
          <tr class="table-secondary table-gradient">
            <th>Date</th>
            <th>Type</th>
            <th>N° Bon Manuel</th>
            <th>Opérateur</th>
            <th>Libellé</th>
            <th class="text-end">Recette</th>
            <th class="text-end">Dépense</th>
            <th class="text-end">Solde</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)):
            foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['type'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['numero_bon_manuscrit'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['operateur'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['libelle'] ?? '') ?></td>
            <td class="text-end"><?= number_format($it['recette'] ?? 0, 2) ?></td>
            <td class="text-end"><?= number_format($it['depense'] ?? 0, 2) ?></td>
            <td class="text-end"><?= number_format($it['solde'] ?? 0, 2) ?></td>
            <td>
              <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'caissier'): ?>
              <a class="btn btn-sm btn-primary" href="?page=caisse&action=edit&id=<?= $it['_id'] ?? '' ?>">Modifier</a>
              <a class="btn btn-sm btn-danger" href="?page=caisse&action=delete&id=<?= $it['_id'] ?? '' ?>"
                onclick="return confirm('Supprimer ?')">Supprimer</a>
              <?php else: ?>
              —
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="9" class="text-center">Aucune opération</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Add Entry Section -->

    <?php if (!class_exists('\\Mpdf\\Mpdf')): ?>
    <div class="alert alert-warning">Le serveur ne dispose pas du générateur PDF (<code>mpdf/mpdf</code>). Cliquez sur
      <strong>Exporter PDF (imprimer)</strong> pour ouvrir une version imprimable et enregistrer en PDF via votre
      navigateur, ou installez <code>composer require mpdf/mpdf</code> pour des PDF côté serveur.
    </div>
    <?php endif; ?>

    <script>
    function exportCaisseToPDFClient() {
      // Collect table HTML
      const table = document.querySelector('.table-responsive .table');
      if (!table) return alert('Tableau introuvable');
      const html = `<!doctype html><html><head><meta charset="utf-8"><title>Livre de caisse</title>` +
        '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">' +
        '</head><body class="p-4">' +
        '<h2>Livre de caisse</h2>' + table.outerHTML +
        '<script>window.onload = function(){ window.print(); }</' + 'script>' +
        '</body></html>';
      const w = window.open('', '_blank');
      w.document.write(html);
      w.document.close();
    }
    </script>
    <div class="card mb-4">
      <div class="card-header bg-light">
        <h5 class="mb-0">Ajouter une opération</h5>
      </div>
      <div class="card-body">
        <form method="post" action="?page=caisse&action=add" class="row g-2">
          <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
          <div class="col-md-2"><input type="date" name="date" class="form-control" required></div>
          <div class="col-md-2">
            <select name="type" class="form-select" required>
              <option value="">Sélectionner type</option>
              <option value="entree">Bon d'entrée</option>
              <option value="sortie">Bon de sortie</option>
            </select>
          </div>
          <div class="col-md-2"><input type="text" name="numero_bon_manuscrit" class="form-control"
              placeholder="N° Bon Manuscrit" required></div>
          <div class="col-md-2"><input type="text" name="operateur" class="form-control" placeholder="Opérateur"
              required></div>
          <div class="col-md-2"><input type="text" name="libelle" class="form-control" placeholder="Libellé" required>
          </div>
          <div class="col-md-1"><input type="number" step="0.01" name="montant" class="form-control"
              placeholder="Montant" required></div>
          <div class="col-md-1"><button class="btn btn-success w-100" type="submit">Ajouter</button></div>
        </form>
      </div>
    </div>

  </div>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>