<?php
if (session_status() === PHP_SESSION_NONE)
  session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Modifier - Caisse';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <h3>Modifier opération</h3>
    <div class="card">
      <div class="card-body">
        <?php if (empty($entry)): ?>
          <div class="alert alert-warning">Opération introuvable</div>
        <?php else: ?>
          <form method="post" action="?page=caisse&action=edit&id=<?= $entry['_id'] ?? '' ?>">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            <div class="mb-3">
              <label>Date</label>
              <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($entry['date'] ?? '') ?>"
                required>
            </div>
            <div class="mb-3">
              <label>Type</label>
              <select name="type" class="form-select" required>
                <option value="entree" <?= ($entry['type'] ?? '') === 'entree' ? 'selected' : '' ?>>Bon d'entrée</option>
                <option value="sortie" <?= ($entry['type'] ?? '') === 'sortie' ? 'selected' : '' ?>>Bon de sortie</option>
              </select>
            </div>
            <div class="mb-3">
              <label>N° Bon Manuscrit</label>
              <input type="text" name="numero_bon_manuscrit" class="form-control"
                value="<?= htmlspecialchars($entry['numero_bon_manuscrit'] ?? '') ?>" required>
            </div>
            <div class=" mb-3">
              <label>Opérateur</label>
              <input type="text" name="operateur" class="form-control"
                value="<?= htmlspecialchars($entry['operateur'] ?? '') ?>" required>
            </div>
            <div class=" mb-3">
              <label>Libellé</label>
              <input type="text" name="libelle" class="form-control"
                value="<?= htmlspecialchars($entry['libelle'] ?? '') ?>" required>
            </div>
            <div class=" mb-3">
              <label>Montant</label>
              <input type="number" step="0.01" name="montant" class="form-control"
                value="<?= htmlspecialchars($entry['montant'] ?? '') ?>" required>
            </div>
            <button class=" btn btn-primary" type="submit">Enregistrer</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>