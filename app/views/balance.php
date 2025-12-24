<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Balance - Comptabilité</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <h2>Balance Comptable</h2>
    <form class="row g-2 mb-3" method="get">
      <input type="hidden" name="page" value="balance">
      <div class="col-md-4"><input type="text" class="form-control" name="compte" placeholder="Compte"
          value="<?= htmlspecialchars($_GET['compte'] ?? '') ?>"></div>
      <div class="col-md-3"><input type="date" class="form-control" name="date_debut"
          value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>"></div>
      <div class="col-md-3"><input type="date" class="form-control" name="date_fin"
          value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>"></div>
      <div class="col-md-2"><button class="btn btn-secondary w-100" type="submit">Filtrer</button></div>
    </form>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Compte</th>
          <th>Débit</th>
          <th>Crédit</th>
          <th>Solde</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($balances)):
          foreach ($balances as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['_id'] ?? '') ?></td>
          <td><?= '$ ' . htmlspecialchars($b['debit'] ?? 0) ?></td>
          <td><?= '$ ' . htmlspecialchars($b['credit'] ?? 0) ?></td>
          <td><?= '$ ' . htmlspecialchars(($b['debit'] ?? 0) - ($b['credit'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</body>

</html>