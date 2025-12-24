<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Grand-Livre - Comptabilité</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <h2>Grand-Livre</h2>
    <form method="get" class="mb-3">
      <input type="hidden" name="page" value="grandlivre">
      <div class="row">
        <div class="col-md-4">
          <select name="compte" class="form-select" onchange="this.form.submit()">
            <?php if (!empty($comptes)):
              foreach ($comptes as $c): ?>
                <option value="<?= htmlspecialchars($c) ?>" <?= ($selected == $c) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c) ?>
                </option>
              <?php endforeach; endif; ?>
          </select>
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
          <th style="width:1%">Actions</th>
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
              <td class="d-flex justify-content-center gap-1">
                <a href="?page=grandlivre&action=edit&id=<?= $entry['_id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="?page=grandlivre&action=delete&id=<?= $entry['_id'] ?>" class="btn btn-sm btn-danger"
                  onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</body>

</html>