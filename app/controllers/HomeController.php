<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\MessageModel;

session_status() === PHP_SESSION_ACTIVE ?: null;

class HomeController extends Controller
{
  public function index()
  {
    // Afficher la page détail d'un service si demandé via GET
    if (isset($_GET['action']) && $_GET['action'] === 'service' && !empty($_GET['id'])) {
      $id = $_GET['id'];
      $serviceModel = new \App\Models\ServiceModel();
      $service = $serviceModel->findById($id);
      $this->render('home/service_detail', ['service' => $service]);
      return;
    }

    // Handle contact form POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_GET['action'] ?? '') === 'contact')) {
      if (session_status() === PHP_SESSION_NONE)
        session_start();
      
      // Vérifier le token CSRF
      $token = $_POST['csrf_token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur de sécurité CSRF - opération annulée.';
        header('Location: ?page=home#contact');
        exit;
      }
      
      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $subject = trim($_POST['subject'] ?? '');
      $message = trim($_POST['message'] ?? '');

      $error = '';
      if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Tous les champs sont requis.';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
      }

      if ($error) {
        $_SESSION['flash_error'] = $error;
      } else {
        $msgModel = new MessageModel();
        $msgModel->insert([
          'name' => $name,
          'email' => $email,
          'subject' => $subject,
          'message' => $message,
          'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        // Try to send notification email to site admin
        $to = 'info@jfbusiness.com';
        $mailSubject = 'Nouveau message reçu - ' . ($subject ?: 'Sans sujet');
        $body = "Nom: $name\nEmail: $email\nSujet: $subject\n\nMessage:\n$message\n\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? '');
        $headers = 'From: no-reply@jfbusiness.com' . "\r\n" . 'Reply-To: ' . $email . "\r\n";
        try {
          @mail($to, $mailSubject, $body, $headers);
        } catch (\Throwable $e) {
          // Ignore mail errors but do not block the user
        }
        $_SESSION['flash'] = 'Merci pour votre message ! Nous vous répondrons bientôt.';
      }

      header('Location: ?page=home#contact');
      exit;
    }

    // Cette page est publique, pas besoin d'authentification
    $this->render('home/home', []);
  }
}
