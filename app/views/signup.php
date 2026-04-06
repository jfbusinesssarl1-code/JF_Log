<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Inscription - Comptabilité';
  require __DIR__ . '/_layout_head.php'; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #0a1628 0%, #1a2332 50%, #2c3e50 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 20px;
    }

    .signup-container {
      max-width: 500px;
      margin: 0 auto;
      width: 100%;
    }

    .signup-card {
      background: white;
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      border: none;
      animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .signup-header {
      background: linear-gradient(135deg, #0a1628 0%, #1a2332 100%);
      padding: 40px 30px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .signup-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(135, 206, 235, 0.1) 0%, transparent 70%);
      animation: pulse 3s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 0.5; }
      50% { transform: scale(1.1); opacity: 0.8; }
    }

    .signup-header h4 {
      color: white;
      font-size: 2rem;
      font-weight: 700;
      margin: 0;
      position: relative;
      z-index: 1;
      letter-spacing: -0.5px;
    }

    .signup-header .icon {
      font-size: 3.5rem;
      color: #87CEEB;
      margin-bottom: 15px;
      display: inline-block;
      position: relative;
      z-index: 1;
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    .signup-body {
      padding: 40px 35px;
    }

    .form-label {
      color: #2c3e50;
      font-weight: 600;
      font-size: 0.95rem;
      margin-bottom: 10px;
    }

    .form-control {
      border: 2px solid #e1e8ed;
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 1rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: #f8f9fa;
    }

    .form-control:focus {
      border-color: #87CEEB;
      box-shadow: 0 0 0 4px rgba(135, 206, 235, 0.15);
      background: white;
      transform: translateY(-2px);
    }

    .form-control::placeholder {
      color: #adb5bd;
    }

    .btn-signup {
      background: linear-gradient(135deg, #87CEEB 0%, #4FC3F7 100%);
      border: none;
      border-radius: 12px;
      padding: 15px 30px;
      font-size: 1.1rem;
      font-weight: 700;
      color: white;
      width: 100%;
      margin-top: 10px;
      box-shadow: 0 8px 20px rgba(135, 206, 235, 0.4);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .btn-signup:hover {
      background: linear-gradient(135deg, #4FC3F7 0%, #29B6F6 100%);
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(135, 206, 235, 0.6);
      color: white;
    }

    .btn-signup:active {
      transform: translateY(-1px);
    }

    .alert {
      border-radius: 12px;
      border: none;
      padding: 15px 20px;
      font-size: 0.95rem;
    }

    .alert-danger {
      background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
      color: #dc3545;
      border-left: 4px solid #dc3545;
    }

    .alert-success {
      background: linear-gradient(135deg, rgba(40, 167, 69, 0.15), rgba(40, 167, 69, 0.08));
      color: #28a745;
      border-left: 4px solid #28a745;
    }

    .alert-success .btn {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      border: none;
      color: white;
      padding: 8px 20px;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .alert-success .btn:hover {
      background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .login-link {
      text-align: center;
      margin-top: 25px;
      padding-top: 25px;
      border-top: 1px solid #e9ecef;
    }

    .login-link a {
      color: #87CEEB;
      text-decoration: none;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      position: relative;
    }

    .login-link a::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      width: 0;
      height: 2px;
      background: #87CEEB;
      transition: width 0.3s ease;
    }

    .login-link a:hover {
      color: #4FC3F7;
    }

    .login-link a:hover::after {
      width: 100%;
    }

    .mb-3 {
      margin-bottom: 1.5rem !important;
    }

    .input-icon {
      position: relative;
    }

    .input-icon i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #87CEEB;
      font-size: 1.2rem;
    }

    .input-icon .form-control {
      padding-left: 50px;
    }

    .text-muted {
      color: #6c757d !important;
      font-size: 0.85rem;
      margin-top: 5px;
      display: block;
    }

    @media (max-width: 576px) {
      .signup-header h4 {
        font-size: 1.6rem;
      }

      .signup-header .icon {
        font-size: 2.8rem;
      }

      .signup-body {
        padding: 30px 25px;
      }
    }
  </style>
</head>

<body>
  <div class="signup-container">
    <div class="signup-card card">
      <div class="signup-header">
        <div class="icon">
          <i class="bi bi-person-plus-fill"></i>
        </div>
        <h4>Créer un compte</h4>
      </div>
      <div class="signup-body">
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
          <div class="alert alert-success mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <div class="mt-3">
              <a href="?page=login" class="btn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter maintenant
              </a>
            </div>
          </div>
        <?php endif; ?>

        <?php if (empty($success)): ?>
          <form method="post" onsubmit="return validateSignupForm();" novalidate>
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            
            <div class="mb-3">
              <label for="username" class="form-label">
                <i class="bi bi-person-fill me-2"></i>Nom d'utilisateur
              </label>
              <div class="input-icon">
                <i class="bi bi-person"></i>
                <input type="text" class="form-control" id="username" name="username" required
                  minlength="3" maxlength="32" pattern="[a-zA-Z0-9_]+" placeholder="Choisissez un nom d'utilisateur">
              </div>
              <small class="text-muted">3-32 caractères, lettres/chiffres/_ uniquement</small>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">
                <i class="bi bi-lock-fill me-2"></i>Mot de passe
              </label>
              <div class="input-icon">
                <i class="bi bi-lock"></i>
                <input type="password" class="form-control" id="password" name="password" required
                  minlength="6" placeholder="Créez un mot de passe">
              </div>
              <small class="text-muted">Minimum 6 caractères</small>
            </div>

            <div class="mb-3">
              <label for="password_confirm" class="form-label">
                <i class="bi bi-lock-fill me-2"></i>Confirmer le mot de passe
              </label>
              <div class="input-icon">
                <i class="bi bi-lock"></i>
                <input type="password" class="form-control" id="password_confirm"
                  name="password_confirm" required minlength="6" placeholder="Confirmez votre mot de passe">
              </div>
            </div>

            <button type="submit" class="btn btn-signup">
              <i class="bi bi-person-plus-fill me-2"></i>S'inscrire
            </button>
          </form>
        <?php endif; ?>

        <div class="login-link">
          <p class="mb-0" style="color: #6c757d;">
            Déjà inscrit ? 
            <a href="?page=login">
              <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
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