<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Journal - Comptabilité</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Journal Comptable</h2>
      <a class="btn btn-primary" style="position: absolute; right: 2%; bottom: 2%;" href="#addForm">+</a>
    </div>
    <form class="row g-2 mb-3" method="get">
      <input type="hidden" name="page" value="journal">
      <div class="col-md-3"><input type="text" class="form-control" name="compte" placeholder="Compte"
          value="<?= htmlspecialchars($filters['compte'] ?? '') ?>"></div>
      <div class="col-md-3"><input type="text" class="form-control" name="lieu" placeholder="Lieu"
          value="<?= htmlspecialchars($filters['lieu'] ?? '') ?>"></div>
      <div class="col-md-2"><input type="date" class="form-control" name="date_debut"
          value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>"></div>
      <div class="col-md-2"><input type="date" class="form-control" name="date_fin"
          value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>"></div>
      <div class="col-md-1"><button class="btn btn-secondary w-100" type="submit">Filtrer</button></div>
      <div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="?page=journal">Afficher tout</a>
      </div>
    </form>

    <div class="table-responsive shadow-sm rounded-3">
      <table class="table table-bordered align-middle mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Compte</th>
            <th>Lieu</th>
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
            <td><?= htmlspecialchars($entry['compte'] ?? '') ?></td>
            <td><?= htmlspecialchars($entry['lieu'] ?? '') ?></td>
            <td><?= htmlspecialchars($entry['libelle'] ?? '') ?></td>
            <td><?= ($entry['debit'] !== '' ? '$ ' . htmlspecialchars($entry['debit']) : '') ?></td>
            <td><?= ($entry['credit'] !== '' ? '$ ' . htmlspecialchars($entry['credit']) : '') ?></td>
            <td class="d-flex justify-content-center gap-1">
              <a href="?page=journal&action=edit&id=<?= $entry['_id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
              <a href="?page=journal&action=delete&id=<?= $entry['_id'] ?>" class="btn btn-sm btn-danger"
                onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <h4 id="addForm" class="mt-4">Ajouter une écriture (partie double)</h4>

    <form method="post" action="?page=journal&action=add" onsubmit="return validateJournalForm();"
      class="card card-body shadow-sm border-0">
      <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
      <div class="row mb-2 g-2">
        <div class="col-md-2">
          <input type="date" name="date" class="form-control" id="date" required>
        </div>
        <div class="col-md-2">
          <input type="text" name="lieu" class="form-control" id="lieu" placeholder="Lieu d'opération" required
            maxlength="64">
        </div>

        <!-- Compte débit -->
        <div class="col-md-2">
          <?php if (!empty($comptes)): ?>
          <select id="compte_debitSelect" name="compte_debitSelect" class="form-select"
            onchange="updateIntitule('debit')">
            <option value="">-- Compte débit --</option>
            <?php foreach ($comptes as $c): ?>
            <option value="<?= htmlspecialchars($c['no']) ?>" data-intitule="<?= htmlspecialchars($c['intitule']) ?>">
              <?= htmlspecialchars($c['no']) ?> - <?= htmlspecialchars($c['intitule']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?php else: ?>
          <input type="text" name="compte_debit" id="compte_debit" class="form-control" placeholder="Compte débit"
            maxlength="32">
          <?php endif; ?>
        </div>

        <div class="col-md-2">
          <input type="text" name="intitule_debit" id="intitule_debitInput" class="form-control"
            placeholder="Intitulé débit" maxlength="64" required>
        </div>

        <!-- Compte crédit -->
        <div class="col-md-2">
          <?php if (!empty($comptes)): ?>
          <select id="compte_creditSelect" name="compte_creditSelect" class="form-select"
            onchange="updateIntitule('credit')">
            <option value="">-- Compte crédit --</option>
            <?php foreach ($comptes as $c): ?>
            <option value="<?= htmlspecialchars($c['no']) ?>" data-intitule="<?= htmlspecialchars($c['intitule']) ?>">
              <?= htmlspecialchars($c['no']) ?> - <?= htmlspecialchars($c['intitule']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?php else: ?>
          <input type="text" name="compte_credit" id="compte_credit" class="form-control" placeholder="Compte crédit"
            maxlength="32">
          <?php endif; ?>
        </div>

        <div class="col-md-2">
          <input type="text" name="intitule_credit" id="intitule_creditInput" class="form-control"
            placeholder="Intitulé crédit" maxlength="64" required>
        </div>

        <div class="col-md-3">
          <input type="text" name="libelle" class="form-control" id="libelle" placeholder="Libellé" required
            maxlength="64">
        </div>

        <div class="col-md-2">
          <input type="number" step="0.01" name="debit" class="form-control" id="debit" placeholder="Débit" required>
        </div>
        <div class="col-md-2">
          <input type="number" step="0.01" name="credit" class="form-control" id="credit" placeholder="Crédit" required>
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-success">Ajouter</button>
        </div>
      </div>
    </form>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateIntitule(side) {
  var select = document.getElementById('compte_' + side + 'Select');
  var intituleInput = document.getElementById('intitule_' + side + 'Input');
  if (!select) return;
  var selected = select.options[select.selectedIndex];
  if (selected && selected.value && selected.dataset.intitule) {
    intituleInput.value = selected.dataset.intitule;
    intituleInput.readOnly = true;
    // also set text input for compte if present
    var textCompte = document.getElementById('compte_' + side);
    if (textCompte) textCompte.value = selected.value;
  } else {
    intituleInput.value = '';
    intituleInput.readOnly = false;
  }
}

function validateJournalForm() {
  const date = document.getElementById('date').value;
  const compteDeb = (document.getElementById('compte_debit') ? document.getElementById('compte_debit').value.trim() : (
    document.getElementById('compte_debitSelect') ? document.getElementById('compte_debitSelect').value : ''));
  const compteCre = (document.getElementById('compte_credit') ? document.getElementById('compte_credit').value.trim() :
    (document.getElementById('compte_creditSelect') ? document.getElementById('compte_creditSelect').value : ''));
  const intituleDeb = document.getElementById('intitule_debitInput').value.trim();
  const intituleCre = document.getElementById('intitule_creditInput').value.trim();
  const libelle = document.getElementById('libelle').value.trim();
  const debit = document.getElementById('debit').value;
  const credit = document.getElementById('credit').value;
  let errors = [];
  if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) errors.push('Date invalide');
  if (compteDeb.length < 1 || compteDeb.length > 32) errors.push('Compte débit invalide');
  if (compteCre.length < 1 || compteCre.length > 32) errors.push('Compte crédit invalide');
  if (intituleDeb.length < 1 || intituleDeb.length > 64) errors.push('Intitulé compte débit invalide');
  if (intituleCre.length < 1 || intituleCre.length > 64) errors.push('Intitulé compte crédit invalide');
  if (libelle.length < 1 || libelle.length > 64) errors.push('Libellé invalide');
  if (!debit || isNaN(debit) || parseFloat(debit) <= 0) errors.push('Débit invalide');
  if (!credit || isNaN(credit) || parseFloat(credit) <= 0) errors.push('Crédit invalide');
  if (!isNaN(debit) && !isNaN(credit) && Math.abs(parseFloat(debit) - parseFloat(credit)) > 0.001) errors.push(
    'Le montant débit doit être égal au montant crédit');
  if (errors.length) {
    alert(errors.join('\n'));
    return false;
  }
  return true;
}
</script>

</html>