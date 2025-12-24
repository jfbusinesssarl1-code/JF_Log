<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Relevé des comptes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <div class="container mt-4">
    <h2>Relevé des comptes</h2>
    <form class="row g-2 mb-3" method="get">
      <input type="hidden" name="page" value="releve">
      <div class="col-md-3"><input type="text" class="form-control" name="compte" placeholder="Compte"
          value="<?= htmlspecialchars($filters['compte'] ?? '') ?>"></div>
      <div class="col-md-3"><input type="text" class="form-control" name="lieu" placeholder="Lieu"
          value="<?= htmlspecialchars($filters['lieu'] ?? '') ?>"></div>
      <div class="col-md-2"><input type="date" class="form-control" name="date_debut"
          value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>"></div>
      <div class="col-md-2"><input type="date" class="form-control" name="date_fin"
          value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>"></div>
      <div class="col-md-2"><button class="btn btn-secondary w-100" type="submit">Filtrer</button></div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Date</th>
            <th>Lieu</th>
            <th>Compte</th>
            <th>Libellé</th>
            <th>Débit</th>
            <th>Crédit</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($entries)):
                        foreach ($entries as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($e['lieu'] ?? '') ?></td>
            <td><?= htmlspecialchars($e['compte'] ?? '') ?></td>
            <td><?= htmlspecialchars($e['libelle'] ?? '') ?></td>
            <td><?= ($e['debit'] !== '' ? '$ ' . htmlspecialchars($e['debit']) : '') ?></td>
            <td><?= ($e['credit'] !== '' ? '$ ' . htmlspecialchars($e['credit']) : '') ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="6" class="text-center">Aucune donnée</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</html>