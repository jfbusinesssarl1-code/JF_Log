<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\UserModel;
class RegisterController extends Controller
{
    public function index()
    {
        // Seul l'admin peut créer de nouveaux utilisateurs
        $this->requireAdmin();

        $error = '';
        $success = '';
        $users = [];

        // Gérer les actions delete et edit
        $action = $_GET['action'] ?? null;
        $userId = $_GET['user_id'] ?? null;

        if ($action === 'delete' && $userId) {
            $token = $_GET['token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                $error = 'Erreur CSRF';
            } else {
                $model = new UserModel();
                $model->delete($userId);
                $success = 'Utilisateur supprimé avec succès';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!\App\Core\Csrf::checkToken($token)) {
                $error = 'Erreur CSRF';
            } else {
                $postAction = $_POST['action'] ?? 'create';

                if ($postAction === 'edit' && $userId) {
                    // Modification d'utilisateur
                    $role = $_POST['role'] ?? 'user';
                    $password = $_POST['password'] ?? '';
                    $validRoles = ['admin', 'manager', 'accountant', 'caissier', 'user'];

                    if (!in_array($role, $validRoles)) {
                        $error = 'Rôle invalide';
                    } else {
                        $model = new UserModel();
                        $model->updateRole($userId, $role);
                        if (!empty($password)) {
                            if (strlen($password) < 6) {
                                $error = "Mot de passe trop court (min. 6 caractères)";
                            } else {
                                $model->updatePassword($userId, $password);
                            }
                        }
                        if (empty($error)) {
                            $success = 'Utilisateur modifié avec succès';
                        }
                    }
                } else {
                    // Création d'utilisateur
                    $username = trim($_POST['username'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $role = $_POST['role'] ?? 'user';

                    // Valider le rôle
                    $validRoles = ['admin', 'manager', 'accountant', 'caissier', 'user'];
                    if (!in_array($role, $validRoles)) {
                        $error = 'Rôle invalide';
                    } elseif (strlen($username) < 3 || strlen($username) > 32 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                        $error = "Nom d'utilisateur invalide (3-32 caractères, lettres/chiffres/_ uniquement)";
                    } elseif (strlen($password) < 6) {
                        $error = "Mot de passe trop court (min. 6 caractères)";
                    } else {
                        $model = new UserModel();
                        if ($model->findByUsername($username)) {
                            $error = 'Nom d\'utilisateur déjà utilisé';
                        } else {
                            $model->create($username, $password, $role);
                            $success = 'Utilisateur créé avec succès (Rôle: ' . ucfirst($role) . ')';
                        }
                    }
                }
            }
        }

        // Afficher la liste des utilisateurs existants
        $userModel = new UserModel();
        $users = $userModel->getAll();

        // Si édition, charger l'utilisateur à éditer
        $editUser = null;
        if ($action === 'edit' && $userId) {
            $editUser = $userModel->findById($userId);
        }

        $this->render('register', ['error' => $error, 'success' => $success, 'users' => $users, 'editUser' => $editUser, 'userId' => $userId]);
    }
}
