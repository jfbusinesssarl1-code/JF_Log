<?php
namespace App\Controllers;
use App\Core\Controller;
class LogoutController extends Controller
{
    public function index()
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        // Clear session data securely
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        // Redirect to public home page after logout
        header('Location: ?page=home');
        exit;
    }
}