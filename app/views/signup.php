<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Inscription - Comptabilité</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-light min-vh-100 d-flex align-items-center"
  style="background: linear-gradient(135deg,#f8fafc,#e2e8f0);">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div class="card shadow rounded-4 border-0">
          <div class="card-header text-center bg-white border-0 py-3">
            <h4 class="mb-0 fw-bold text-secondary">Créer un compte</h4>
          </div>
          <div class="card-body">
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger text-center shadow-sm"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
              <div class="alert alert-success text-center shadow-sm">
                <?= htmlspecialchars($success) ?><br>
                <a href="?page=login" class="btn btn-sm btn-primary mt-2">Se connecter maintenant</a>
              </div>
            <?php endif; ?>

            <?php if (empty($success)): ?>
              <form method="post" onsubmit="return validateSignupForm();" novalidate>
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
                <div class="mb-3">
                  <label for="username" class="form-label">Nom d'utilisateur</label>
                  <input type="text" class="form-control form-control-lg" id="username" name="username" required
                    minlength="3" maxlength="32" pattern="[a-zA-Z0-9_]+" placeholder="ex: jdoe">
                  <small class="text-muted">3-32 caractères, lettres/chiffres/_ uniquement</small>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Mot de passe</label>
                  <input type="password" class="form-control form-control-lg" id="password" name="password" required
                    minlength="6" placeholder="••••••••">
                  <small class="text-muted">Minimum 6 caractères</small>
                </div>
                <div class="mb-3">
                  <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                  <input type="password" class="form-control form-control-lg" id="password_confirm"
                    name="password_confirm" required minlength="6" placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-success w-100 btn-lg shadow-sm">S'inscrire</button>
              </form>
            <?php endif; ?>

            <div class="text-center mt-3">
              <p class="mb-0">Déjà inscrit ? <a href="?page=login" class="text-decoration-none fw-bold">Se connecter</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
<script>
  function validateSignupForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    let errors = [];

    if (username.length < 3 || username.length > 32 || !/^[a-zA-Z0-9_]+$/.test(username)) {
      errors.push("Nom d'utilisateur invalide (3-32 caractères, lettres/chiffres/_ uniquement)");
    }
    if (password.length < 6) {
      errors.push("Mot de passe trop court (min. 6 caractères)");
    }
    if (password !== passwordConfirm) {
      errors.push("Les mots de passe ne correspondent pas");
    }

    if (errors.length) {
      alert(errors.join('\n'));
      return false;
    }
    return true;
  }
</script>

</html>