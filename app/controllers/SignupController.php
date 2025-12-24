<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\UserModel;

class SignupController extends Controller
{
  public function index()
  {
    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $token = $_POST['csrf_token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $error = 'Erreur CSRF';
      } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validations
        if (strlen($username) < 3 || strlen($username) > 32 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
          $error = "Nom d'utilisateur invalide (3-32 caractères, lettres/chiffres/_ uniquement)";
        } elseif (strlen($password) < 6) {
          $error = "Mot de passe trop court (min. 6 caractères)";
        } elseif ($password !== $password_confirm) {
          $error = "Les mots de passe ne correspondent pas";
        } else {
          $model = new UserModel();
          if ($model->findByUsername($username)) {
            $error = 'Nom d\'utilisateur déjà utilisé';
          } else {
            // Créer l'utilisateur avec le rôle par défaut 'user'
            $model->create($username, $password, 'user');
            $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
          }
        }
      }
    }

    $this->render('signup', ['error' => $error, 'success' => $success]);
  }
}
