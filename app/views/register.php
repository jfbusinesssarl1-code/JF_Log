<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Gestion utilisateurs - Comptabilité';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body class="bg-light">

  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <div class="row">
      <!-- Formulaire de création utilisateur -->
      <div class="col-md-5">
        <div class="card shadow rounded-3 border-0">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0">
              <?= !empty($editUser) ? 'Modifier l\'utilisateur' : 'Créer un nouvel utilisateur' ?>
            </h5>
          </div>
          <div class="card-body">
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="post" onsubmit="return validateRegisterForm();" novalidate>
              <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
              <?php if (!empty($editUser)): ?>
              <input type="hidden" name="action" value="edit">
              <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" disabled value="<?= htmlspecialchars($editUser['username']) ?>">
              </div>
              <?php else: ?>
              <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required minlength="3"
                  maxlength="32" pattern="[a-zA-Z0-9_]+" placeholder="ex: jdoe">
              </div>
              <?php endif; ?>
              <div class="mb-3">
                <label for="password" class="form-label">Mot de
                  passe<?= !empty($editUser) ? ' (laisser vide pour ne pas changer)' : '' ?></label>
                <input type="password" class="form-control" id="password" name="password"
                  <?= empty($editUser) ? 'required' : '' ?> minlength="6" placeholder="••••••••">
              </div>
              <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role" required>
                  <option value="user" <?= ($editUser['role'] ?? '') === 'user' ? 'selected' : '' ?>>
                    Utilisateur (user)</option>
                  <option value="accountant" <?= ($editUser['role'] ?? '') === 'accountant' ? 'selected' : '' ?>>
                    Comptable (accountant)</option>
                  <option value="caissier" <?= ($editUser['role'] ?? '') === 'caissier' ? 'selected' : '' ?>>Caissier
                    (caissier)</option>
                  <option value="manager" <?= ($editUser['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Gestionnaire
                    (manager)</option>
                  <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                    Administrateur (admin)</option>
                </select>
              </div>
              <div class="d-flex gap-2">
                <button type="submit"
                  class="btn btn-success flex-grow-1"><?= !empty($editUser) ? 'Enregistrer les modifications' : 'Créer l\'utilisateur' ?></button>
                <?php if (!empty($editUser)): ?>
                <a href="?page=register" class="btn btn-secondary">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Liste des utilisateurs existants -->
      <div class="col-md-7">
        <div class="card shadow rounded-3 border-0">
          <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Utilisateurs existants</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Rôle</th>
                    <th>Créé le</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($users)): ?>
                  <?php foreach ($users as $user): ?>
                  <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                      <span class="badge bg-info"><?= htmlspecialchars($user['role'] ?? 'user') ?></span>
                    </td>
                    <td><?= htmlspecialchars($user['created_at'] ?? 'N/A') ?></td>
                    <td class="d-flex align-items-center gap-3">
                      <a href="?page=register&action=edit&user_id=<?= urlencode($user['_id']) ?>"
                        class="btn btn-sm btn-primary">✏️ </a>
                      <a href="?page=register&action=delete&user_id=<?= urlencode($user['_id']) ?>&token=<?= urlencode(\App\Core\Csrf::getToken()) ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">🗑️
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted">Aucun utilisateur</td>
                  </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
  <script>
  function validateRegisterForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    let errors = [];
    if (username.length < 3 || username.length > 32 || !/^[a-zA-Z0-9_]+$/.test(username)) {
      errors.push("Nom d'utilisateur invalide (3-32 caractères, lettres/chiffres/_ uniquement)");
    }
    if (password.length < 6) {
      errors.push("Mot de passe trop court (min. 6 caractères)");
    }
    if (errors.length) {
      alert(errors.join('\n'));
      return false;
    }
    return true;
  }
  </script>

</html>