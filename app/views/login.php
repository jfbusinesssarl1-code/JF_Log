<!DOCTYPE html>
<html lang="fr">

<head>
    <?php $title = 'Connexion - Comptabilité';
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

        .login-container {
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
        }

        .login-card {
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

        .login-header {
            background: linear-gradient(135deg, #0a1628 0%, #1a2332 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
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

        .login-header h4 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .login-header .icon-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .login-logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            border: 2px solid #87CEEB;
            box-shadow: 0 4px 15px rgba(135, 206, 235, 0.4);
            background: linear-gradient(135deg, rgba(135, 206, 235, 0.2), rgba(79, 195, 247, 0.3));
            padding: 6px;
            object-fit: contain;
        }

        .login-header .icon {
            font-size: 3.5rem;
            color: #87CEEB;
            display: inline-block;
            position: relative;
            z-index: 1;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .login-body {
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

        .btn-login {
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

        .btn-login:hover {
            background: linear-gradient(135deg, #4FC3F7 0%, #29B6F6 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(135, 206, 235, 0.6);
            color: white;
        }

        .btn-login:active {
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

        .alert-info {
            background: linear-gradient(135deg, rgba(135, 206, 235, 0.15), rgba(135, 206, 235, 0.08));
            color: #0a1628;
            border-left: 4px solid #87CEEB;
        }

        .alert-info code {
            background: rgba(10, 22, 40, 0.1);
            padding: 3px 8px;
            border-radius: 6px;
            color: #0a1628;
            font-weight: 600;
        }

        .signup-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
        }

        .signup-link a {
            color: #87CEEB;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .signup-link a::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 2px;
            background: #87CEEB;
            transition: width 0.3s ease;
        }

        .signup-link a:hover {
            color: #4FC3F7;
        }

        .signup-link a:hover::after {
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

        @media (max-width: 576px) {
            .login-header h4 {
                font-size: 1.6rem;
            }

            .login-header .icon {
                font-size: 2.8rem;
            }

            .login-logo {
                width: 46px;
                height: 46px;
                border-radius: 12px;
                padding: 5px;
            }

            .login-body {
                padding: 30px 25px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="login-header">
                <div class="icon-row">
                    <img src="/asset.php?f=images/logo.png" alt="Logo" class="login-logo">
                    <div class="icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
                <h4>Connexion</h4>
            </div>
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" onsubmit="return validateLoginForm();" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person-fill me-2"></i>Nom d'utilisateur
                        </label>
                        <div class="input-icon">
                            <i class="bi bi-person"></i>
                            <input type="text" class="form-control" id="username" name="username"
                                required minlength="3" maxlength="32" pattern="[a-zA-Z0-9_]+"
                                placeholder="Entrez votre nom d'utilisateur">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock-fill me-2"></i>Mot de passe
                        </label>
                        <div class="input-icon">
                            <i class="bi bi-lock"></i>
                            <input type="password" class="form-control" id="password"
                                name="password" required minlength="6" placeholder="Entrez votre mot de passe">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </button>
                </form>

                <div class="signup-link">
                    <a href="?page=signup">
                        <i class="bi bi-person-plus-fill me-2"></i>Créer un compte
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
        function validateLoginForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            let errors = [];
            if (username.length < 3 || username.length > 32 || !/^[a-zA-Z0-9_]+$/.test(username)) {
                errors.push("Nom d'utilisateur invalide");
            }
            if (password.length < 6) {
                errors.push("Mot de passe trop court");
            }
            if (errors.length) {
                alert(errors.join('\n'));
                return false;
            }
            return true;
        }
    </script>

</html>