<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Modifier - Grand-Livre';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-secondary mb-4">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="?page=dashboard" style="margin-right:10%;">
        Compta
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- <div class="collapse navbar-collapse" id="navbarNav"> -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="?page=journal">Journal</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=grandlivre">Grand-Livre</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=balance">Balance</a></li>
        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link text-success fw-bold" href="?page=register">⚙️ Gestion
              Utilisateurs</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><span class="nav-link">👤
            <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?>
            (<?= htmlspecialchars($_SESSION['user']['role'] ?? 'user') ?>)</span></li>
        <li class="nav-item"><a class="nav-link text-danger fw-bold" href="?page=logout">Déconnexion</a></li>
      </ul>
      <!-- </div> -->
    </div>
  </nav>
  <div class="container mt-4">
    <h2>Modifier une opération</h2>
    <?php if ($entry): ?>
      <form method="post">
        <div class="mb-3">
          <label for="date" class="form-label">Date</label>
          <input type="date" name="date" id="date" class="form-control"
            value="<?= htmlspecialchars($entry['date'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label for="libelle" class="form-label">Libellé</label>
          <input type="text" name="libelle" id="libelle" class="form-control"
            value="<?= htmlspecialchars($entry['libelle'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label for="debit" class="form-label">Débit</label>
          <input type="number" step="0.01" name="debit" id="debit" class="form-control"
            value="<?= htmlspecialchars($entry['debit'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label for="credit" class="form-label">Crédit</label>
          <input type="number" step="0.01" name="credit" id="credit" class="form-control"
            value="<?= htmlspecialchars($entry['credit'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="?page=grandlivre&compte=<?= urlencode($selected) ?>" class="btn btn-secondary">Annuler</a>
      </form>
    <?php else: ?>
      <div class="alert alert-danger">Opération introuvable.</div>
    <?php endif; ?>
  </div>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>