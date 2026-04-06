<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Models\MessageModel;
use App\Helpers\ImageConverterV2;
use Exception;

class AdminController extends Controller
{
  public function index()
  {
    // Vérifier que l'utilisateur est admin ou manager
    $this->requireRole(['admin', 'manager']);

    $currentSection = $_GET['section'] ?? 'dashboard';

    // Handle delete/edit actions for home carousel
    if ($currentSection === 'home' && ($_GET['action'] ?? '') === 'delete') {
      $this->handleHomeActions($currentSection);
      return; // Will redirect
    }
    // Handle delete actions for activities
    if ($currentSection === 'activities' && ($_GET['action'] ?? '') === 'delete') {
      $this->handleActivityActions($currentSection);
      return; // Will redirect
    }
    // Handle delete/edit actions for about section
    if ($currentSection === 'about' && ($_GET['action'] ?? '') === 'delete') {
      $this->handleAboutActions($currentSection);
      return; // Will redirect
    }
    // Handle delete actions for services
    if ($currentSection === 'services' && ($_GET['action'] ?? '') === 'delete') {
      $this->handleServiceActions($currentSection);
      return; // Will redirect
    }
    // Handle delete actions for partners
    if ($currentSection === 'partners' && ($_GET['action'] ?? '') === 'delete') {
      $this->handlePartnerActions($currentSection);
      return; // Will redirect
    }
    // Handle delete actions for users and messages
    if ($currentSection === 'users' && ($_GET['action'] ?? '') === 'delete') {
      $this->handleUserActions($currentSection);
      return; // Will redirect
    }
    if ($currentSection === 'messages' && ($_GET['action'] ?? '') === 'delete') {
      $this->handleMessageActions($currentSection);
      return;
    }
    if ($currentSection === 'messages' && ($_GET['action'] ?? '') === 'export') {
      $this->exportMessagesCsv();
      return;
    }
    if ($currentSection === 'messages' && ($_GET['action'] ?? '') === 'mark_read') {
      $this->markMessageAsRead();
      return;
    }

    // If POST, handle form submission for the current section
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($currentSection === 'users') {
        $this->handleUserPost();
      } else {
        $this->handlePost($currentSection);
      }
      // After handling, reload admin page to show changes / flash
      header('Location: ?page=admin&section=' . urlencode($currentSection));
      exit;
    }

    $data = [
      'currentSection' => $currentSection,
      'userRole' => $_SESSION['user']['role'] ?? 'user'
    ];

    // For users section, add user management data
    if ($currentSection === 'users') {
      $userModel = new UserModel();
      $data['users'] = $userModel->getAll();
      $data['error'] = $_SESSION['flash_error'] ?? '';
      $data['success'] = $_SESSION['flash'] ?? '';
      $data['editUser'] = null;
      $data['userId'] = null;

      unset($_SESSION['flash_error']);

      // If editing, load user data
      if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['user_id'])) {
        $data['editUser'] = $userModel->findById($_GET['user_id']);
        $data['userId'] = $_GET['user_id'];
      }
    }

    // For home section, load carousel items
    if ($currentSection === 'home') {
      $homeModel = new \App\Models\HomeModel();
      $data['homeItems'] = $homeModel->getAll();
      $data['error'] = $_SESSION['flash_error'] ?? '';
      $data['success'] = $_SESSION['flash'] ?? '';
      $data['editItem'] = null;
      $data['editItemId'] = null;

      // If editing, load item data
      if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['item_id'])) {
        $data['editItem'] = $homeModel->findById($_GET['item_id']);
        $data['editItemId'] = $_GET['item_id'];
      }

      unset($_SESSION['flash_error']);
    }

    // For about section, load about items
    if ($currentSection === 'about') {
      $aboutModel = new \App\Models\AboutModel();
      $data['aboutItems'] = $aboutModel->getAll();
      $data['error'] = $_SESSION['flash_error'] ?? '';
      $data['success'] = $_SESSION['flash'] ?? '';
      $data['editItem'] = null;
      $data['editItemId'] = null;

      // If editing, load item data
      if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['item_id'])) {
        $data['editItem'] = $aboutModel->findById($_GET['item_id']);
        $data['editItemId'] = $_GET['item_id'];
      }

      unset($_SESSION['flash_error']);
    }

    // For services section, load services
    if ($currentSection === 'services') {
      $serviceModel = new \App\Models\ServiceModel();
      $data['services'] = $serviceModel->getAll();
      $data['error'] = $_SESSION['flash_error'] ?? '';
      $data['success'] = $_SESSION['flash'] ?? '';
      $data['editService'] = null;
      $data['editServiceId'] = null;

      // If editing, load service data
      if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['service_id'])) {
        $data['editService'] = $serviceModel->findById($_GET['service_id']);
        $data['editServiceId'] = $_GET['service_id'];
      }

      unset($_SESSION['flash_error']);
    }

    // For activities section, load activities
    if ($currentSection === 'activities') {
      $activityModel = new \App\Models\ActivityModel();
      $data['activities'] = $activityModel->getAll();
      $data['error'] = $_SESSION['flash_error'] ?? '';
      $data['success'] = $_SESSION['flash'] ?? '';
      $data['editActivity'] = null;
      $data['editActivityId'] = null;

      // If editing, load activity data
      if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['activity_id'])) {
        $data['editActivity'] = $activityModel->findById($_GET['activity_id']);
        $data['editActivityId'] = $_GET['activity_id'];
      }

      unset($_SESSION['flash_error']);
    }

    // For messages section, load messages
    if ($currentSection === 'messages') {
      $msgModel = new MessageModel();
      $page = max(1, (int) ($_GET['p'] ?? 1));
      $perPage = max(5, min(100, (int) ($_GET['per'] ?? 10)));
      $q = trim($_GET['q'] ?? '');
      $pag = $msgModel->getPaginated($page, $perPage, $q);
      $data['messages'] = $pag['items'];
      $data['messages_total'] = $pag['total'];
      $data['messages_page'] = $page;
      $data['messages_perPage'] = $perPage;
      $data['messages_query'] = $q;
      $data['error'] = $_SESSION['flash_error'] ?? '';
      $data['success'] = $_SESSION['flash'] ?? '';
      unset($_SESSION['flash_error']);
    }

    // For partners section, load partners
    if ($currentSection === 'partners') {
      $partnerModel = new \App\Models\PartnerModel();
      $data['partners'] = $partnerModel->getAll();
      $data['error'] = $_SESSION['flash_error'] ?? '';
      $data['success'] = $_SESSION['flash'] ?? '';
      $data['editPartner'] = null;
      $data['editPartnerId'] = null;

      // If editing, load partner data
      if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['partner_id'])) {
        $data['editPartner'] = $partnerModel->findById($_GET['partner_id']);
        $data['editPartnerId'] = $_GET['partner_id'];
      }

      unset($_SESSION['flash_error']);
      unset($_SESSION['flash']);
    }

    $this->render('admin/admin_dashboard', $data);
  }

  /**
   * Handle POST for admin sections (basic file storage + JSON)
   */
  protected function handlePost($section)
  {
    // Ensure session started
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    // Vérifier le token CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!\App\Core\Csrf::checkToken($token)) {
      $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
      return;
    }

    // Base data folder
    $dataDir = __DIR__ . '/../../data';
    if (!is_dir($dataDir)) {
      mkdir($dataDir, 0755, true);
    }

    // Uploads folder under public
    $uploadBase = __DIR__ . '/../../public/uploads/admin';
    if (!is_dir($uploadBase)) {
      mkdir($uploadBase, 0755, true);
    }

    switch ($section) {
      case 'home':
        $targetDir = $uploadBase . '/home';
        if (!is_dir($targetDir))
          mkdir($targetDir, 0755, true);
        $item = [
          'title' => $_POST['title'] ?? '',
          'description' => $_POST['description'] ?? '',
          'link' => $_POST['link'] ?? ''
        ];
        if (!empty($_FILES['image']['name'])) {
          // Convertir et optimiser l'image
          $imagePath = ImageConverterV2::convertUploadedFile($_FILES['image'], 'admin/home', 'webp');
          if ($imagePath === false) {
            // Fallback: utiliser l'image originale si la conversion échoue
            $tmp = $_FILES['image']['tmp_name'];
            $name = time() . '_' . basename($_FILES['image']['name']);
            $dest = $targetDir . '/' . $name;
            move_uploaded_file($tmp, $dest);
            $item['image'] = '/uploads/admin/home/' . $name;
          } else {
            $item['image'] = $imagePath;
          }
        }
        // Persist to MongoDB
        $homeModel = new \App\Models\HomeModel();
        if (!empty($_POST['item_id'])) {
          // Update existing item
          $homeModel->update($_POST['item_id'], $item);
          $_SESSION['flash'] = 'Élément du carrousel modifié.';
        } else {
          // Insert new item
          $homeModel->insert($item);
          $_SESSION['flash'] = 'Élément du carrousel ajouté.';
        }
        break;

      case 'about':
        $targetDir = $uploadBase . '/about';
        if (!is_dir($targetDir))
          mkdir($targetDir, 0755, true);

        $item = [
          'title' => $_POST['aboutTitle'] ?? '',
          'text' => $_POST['aboutText'] ?? '',
          'sections' => [],
          'images' => []
        ];

        // Traiter les sections multiples
        if (!empty($_POST['sections']) && is_array($_POST['sections'])) {
          foreach ($_POST['sections'] as $section) {
            if (!empty($section['subtitle']) && !empty($section['text'])) {
              $item['sections'][] = [
                'subtitle' => $section['subtitle'],
                'text' => $section['text']
              ];
            }
          }
        }

        // Traiter les images existantes
        if (!empty($_POST['existing_images']) && is_array($_POST['existing_images'])) {
          foreach ($_POST['existing_images'] as $existingImg) {
            if (!empty($existingImg)) {
              $item['images'][] = $existingImg;
            }
          }
        }

        // Traiter les nouveaux fichiers images avec name="aboutImage[]"
        if (!empty($_FILES['aboutImage'])) {
          $files = $_FILES['aboutImage'];
          // Vérifier si c'est un array (plusieurs fichiers) ou un seul fichier
          if (is_array($files['name'])) {
            // Plusieurs fichiers
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
              if (!empty($files['name'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
                // Convertir et optimiser chaque image
                $singleFile = [
                  'name' => $files['name'][$i],
                  'tmp_name' => $files['tmp_name'][$i],
                  'error' => $files['error'][$i],
                  'size' => $files['size'][$i] ?? 0
                ];
                $imagePath = ImageConverterV2::convertUploadedFile($singleFile, 'admin/about', 'webp');
                if ($imagePath !== false) {
                  $item['images'][] = $imagePath;
                } else {
                  // Fallback: utiliser l'image originale
                  $tmp = $files['tmp_name'][$i];
                  $name = time() . '_' . mt_rand(1000, 9999) . '_' . basename($files['name'][$i]);
                  $dest = $targetDir . '/' . $name;
                  if (move_uploaded_file($tmp, $dest)) {
                    $item['images'][] = '/uploads/admin/about/' . $name;
                  }
                }
              }
            }
          } else {
            // Un seul fichier (si jamais)
            if (!empty($files['name']) && $files['error'] === UPLOAD_ERR_OK) {
              $tmp = $files['tmp_name'];
              $name = time() . '_' . mt_rand(1000, 9999) . '_' . basename($files['name']);
              $dest = $targetDir . '/' . $name;
              if (move_uploaded_file($tmp, $dest)) {
                $item['images'][] = '/uploads/admin/about/' . $name;
              }
            }
          }
        }

        $aboutModel = new \App\Models\AboutModel();
        if (!empty($_POST['item_id'])) {
          // Update existing item
          $aboutModel->update($_POST['item_id'], $item);
          $_SESSION['flash'] = 'Article À propos modifié avec succès.';
        } else {
          // Insert new item
          $aboutModel->insert($item);
          $_SESSION['flash'] = 'Article À propos créé avec succès.';
        }
        break;

      case 'services':
        $targetDir = $uploadBase . '/services';
        if (!is_dir($targetDir))
          mkdir($targetDir, 0755, true);
        $item = [
          'name' => $_POST['serviceName'] ?? '',
          'description' => $_POST['serviceDescription'] ?? ''
        ];
        if (!empty($_FILES['serviceIcon']['name'])) {
          // Utiliser ImageConverterV2 pour optimiser l'icône du service
          $imagePath = ImageConverterV2::convertUploadedFile($_FILES['serviceIcon'], 'admin/services', 'webp');
          
          if ($imagePath === false) {
            // Fallback: utiliser l'image originale si la conversion échoue
            $tmp = $_FILES['serviceIcon']['tmp_name'];
            $name = time() . '_' . basename($_FILES['serviceIcon']['name']);
            $dest = $targetDir . '/' . $name;
            move_uploaded_file($tmp, $dest);
            $item['icon'] = '/uploads/admin/services/' . $name;
          } else {
            // Utiliser le chemin converti
            $item['icon'] = $imagePath;
          }
        }
        $serviceModel = new \App\Models\ServiceModel();
        if (!empty($_POST['service_id'])) {
          // Update existing service
          // If no new icon uploaded, keep existing icon
          if (empty($_FILES['serviceIcon']['name'])) {
            $existingService = $serviceModel->findById($_POST['service_id']);
            if (!empty($existingService['icon'])) {
              $item['icon'] = $existingService['icon'];
            }
          }
          $serviceModel->update($_POST['service_id'], $item);
          $_SESSION['flash'] = 'Service modifié avec succès.';
        } else {
          // Insert new service
          $serviceModel->insert($item);
          $_SESSION['flash'] = 'Service ajouté avec succès.';
        }
        break;

      case 'activities':
        $targetDir = $uploadBase . '/activities';
        if (!is_dir($targetDir))
          mkdir($targetDir, 0755, true);
        $item = [
          'title' => $_POST['activityTitle'] ?? '',
          'description' => $_POST['activityDescription'] ?? '',
          'status' => $_POST['activityStatus'] ?? '',
          'date' => $_POST['activityDate'] ?? ''
        ];
        if (!empty($_FILES['activityImage']['name'])) {
          // DEBUG: Log l'upload
          error_log('AdminController: Upload activity image: ' . $_FILES['activityImage']['name']);
          error_log('AdminController: File error code: ' . $_FILES['activityImage']['error']);
          error_log('AdminController: File size: ' . $_FILES['activityImage']['size']);
          
          // Convertir et optimiser l'image de l'activité
          $imagePath = ImageConverterV2::convertUploadedFile($_FILES['activityImage'], 'admin/activities', 'webp');
          
          if ($imagePath === false) {
            error_log('AdminController: ImageConverterV2 returned false, fallback to move_uploaded_file');
            // Fallback: utiliser l'image originale si la conversion échoue
            $tmp = $_FILES['activityImage']['tmp_name'];
            $name = time() . '_' . basename($_FILES['activityImage']['name']);
            $dest = $targetDir . '/' . $name;
            move_uploaded_file($tmp, $dest);
            $item['image'] = '/uploads/admin/activities/' . $name;
            error_log('AdminController: Fallback image saved: ' . $item['image']);
          } else {
            error_log('AdminController: ImageConverterV2 success: ' . $imagePath);
            $item['image'] = $imagePath;
          }
        } else {
          error_log('AdminController: No image in $_FILES[activityImage]');
        }
        error_log('AdminController: Item to insert/update: ' . json_encode($item));
        
        $activityModel = new \App\Models\ActivityModel();
        try {
          if (!empty($_POST['activity_id'])) {
            // update existing
            $activityModel->update($_POST['activity_id'], $item);
            $_SESSION['flash'] = 'Activité modifiée.';
            error_log('AdminController: Activity updated: ' . $_POST['activity_id']);
          } else {
            // Insert new
            $activityModel->insert($item);
            $_SESSION['flash'] = 'Activité ajoutée.';
            error_log('AdminController: Activity inserted');
          }
        } catch (Exception $e) {
          error_log('AdminController: ERROR during insert/update: ' . $e->getMessage());
          $_SESSION['flash_error'] = 'Erreur: ' . $e->getMessage();
        }
        break;

      case 'partners':
        $targetDir = $uploadBase . '/partners';
        if (!is_dir($targetDir))
          mkdir($targetDir, 0755, true);
        $item = [
          'name' => $_POST['partnerName'] ?? '',
          'link' => $_POST['partnerLink'] ?? ''
        ];
        
        $partnerModel = new \App\Models\PartnerModel();
        
        // Handle logo upload
        if (!empty($_FILES['partnerLogo']['name'])) {
          $tmp = $_FILES['partnerLogo']['tmp_name'];
          $name = time() . '_' . basename($_FILES['partnerLogo']['name']);
          $dest = $targetDir . '/' . $name;
          move_uploaded_file($tmp, $dest);
          $item['logo'] = '/uploads/admin/partners/' . $name;
        }
        
        // Check if we're updating or inserting
        if (!empty($_POST['partner_id'])) {
          // Update existing partner
          // If no new logo, keep the old one
          if (empty($item['logo'])) {
            $existingPartner = $partnerModel->findById($_POST['partner_id']);
            if ($existingPartner && !empty($existingPartner['logo'])) {
              $item['logo'] = $existingPartner['logo'];
            }
          }
          $partnerModel->update($_POST['partner_id'], $item);
          $_SESSION['flash'] = 'Partenaire modifié avec succès.';
        } else {
          // Insert new partner
          $partnerModel->insert($item);
          $_SESSION['flash'] = 'Partenaire ajouté.';
        }
        break;

      default:
        $_SESSION['flash'] = 'Action non reconnue.';
        break;
    }
  }

  protected function appendJson($file, $item)
  {
    $data = [];
    if (file_exists($file)) {
      $json = file_get_contents($file);
      $data = json_decode($json, true) ?? [];
    }
    $data[] = $item;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }

  /**
   * Handle user create/edit POST actions
   */
  protected function handleUserPost()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $error = '';
    $success = '';
    $userModel = new UserModel();

    $token = $_POST['csrf_token'] ?? '';
    if (!\App\Core\Csrf::checkToken($token)) {
      $error = 'Erreur CSRF';
    } else {
      $postAction = $_POST['action'] ?? 'create';

      if ($postAction === 'edit' && !empty($_POST['user_id'])) {
        // Edit user
        $userId = $_POST['user_id'];
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';
        $validRoles = ['admin', 'manager', 'accountant', 'caissier', 'stock_manager', 'user'];

        if (!in_array($role, $validRoles)) {
          $error = 'Rôle invalide';
        } else {
          $userModel->updateRole($userId, $role);
          if (!empty($password)) {
            if (strlen($password) < 6) {
              $error = "Mot de passe trop court (min. 6 caractères)";
            } else {
              $userModel->updatePassword($userId, $password);
            }
          }
          if (empty($error)) {
            $success = 'Utilisateur modifié avec succès';
            $_SESSION['flash'] = $success;
          }
        }
      } else {
        // Create user
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        $validRoles = ['admin', 'manager', 'accountant', 'caissier', 'stock_manager', 'user'];
        if (!in_array($role, $validRoles)) {
          $error = 'Rôle invalide';
        } elseif (strlen($username) < 3 || strlen($username) > 32 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
          $error = "Nom d'utilisateur invalide (3-32 caractères, lettres/chiffres/_ uniquement)";
        } elseif (strlen($password) < 6) {
          $error = "Mot de passe trop court (min. 6 caractères)";
        } else {
          if ($userModel->findByUsername($username)) {
            $error = "Nom d'utilisateur déjà utilisé";
          } else {
            $userModel->create($username, $password, $role);
            $success = 'Utilisateur créé avec succès';
            $_SESSION['flash'] = $success;
          }
        }
      }
    }

    if (!empty($error)) {
      $_SESSION['flash_error'] = $error;
    }
  }

  /**
   * Handle GET actions for users (delete)
   */
  protected function handleUserActions($section)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $action = $_GET['action'] ?? null;
    $userId = $_GET['user_id'] ?? null;

    if ($action === 'delete' && $userId) {
      $token = $_GET['token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur CSRF';
      } else {
        $userModel = new UserModel();
        $userModel->delete($userId);
        $_SESSION['flash'] = 'Utilisateur supprimé avec succès';
      }
    }

    header('Location: ?page=admin&section=users');
    exit;
  }

  /**
   * Handle GET actions for messages (delete)
   */
  protected function handleMessageActions($section)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $action = $_GET['action'] ?? null;
    $messageId = $_GET['message_id'] ?? null;

    if ($action === 'delete' && $messageId) {
      $token = $_GET['token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur CSRF';
      } else {
        $msgModel = new MessageModel();
        $msgModel->delete($messageId);
        $_SESSION['flash'] = 'Message supprimé avec succès';
      }
    }

    header('Location: ?page=admin&section=messages');
    exit;
  }

  /**
   * Mark a message as read (AJAX endpoint)
   */
  protected function markMessageAsRead()
  {
    header('Content-Type: application/json');
    
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $messageId = $_GET['message_id'] ?? null;
    
    if (!$messageId) {
      echo json_encode(['success' => false, 'error' => 'ID manquant']);
      exit;
    }

    $msgModel = new MessageModel();
    $success = $msgModel->markAsRead($messageId);
    
    echo json_encode(['success' => $success]);
    exit;
  }

  /**
   * Handle GET actions for home carousel (delete)
   */
  protected function handleHomeActions($section)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $action = $_GET['action'] ?? null;
    $itemId = $_GET['item_id'] ?? null;

    if ($action === 'delete' && $itemId) {
      $token = $_GET['token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur CSRF';
      } else {
        $homeModel = new \App\Models\HomeModel();
        $homeModel->delete($itemId);
        $_SESSION['flash'] = 'Élément du carrousel supprimé avec succès';
      }
    }

    header('Location: ?page=admin&section=home');
    exit;
  }

  protected function handleAboutActions($section)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $action = $_GET['action'] ?? null;
    $itemId = $_GET['item_id'] ?? null;

    if ($action === 'delete' && $itemId) {
      $token = $_GET['token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur CSRF';
      } else {
        $aboutModel = new \App\Models\AboutModel();
        $aboutModel->delete($itemId);
        $_SESSION['flash'] = 'Élément À propos supprimé avec succès';
      }
    }

    header('Location: ?page=admin&section=about');
    exit;
  }

  protected function handleServiceActions($section)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $action = $_GET['action'] ?? null;
    $serviceId = $_GET['service_id'] ?? null;

    if ($action === 'delete' && $serviceId) {
      $token = $_GET['token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
      } else {
        $serviceModel = new \App\Models\ServiceModel();
        $serviceModel->delete($serviceId);
        $_SESSION['flash'] = 'Service supprimé avec succès';
      }
    }

    header('Location: ?page=admin&section=services');
    exit;
  }

  protected function handleActivityActions($section)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $action = $_GET['action'] ?? null;
    $activityId = $_GET['activity_id'] ?? null;

    if ($action === 'delete' && $activityId) {
      $token = $_GET['token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
      } else {
        $activityModel = new \App\Models\ActivityModel();
        $activityModel->delete($activityId);
        $_SESSION['flash'] = 'Activité supprimée avec succès';
      }
    }

    header('Location: ?page=admin&section=activities');
    exit;
  }

  protected function handlePartnerActions($section)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $action = $_GET['action'] ?? null;
    $partnerId = $_GET['partner_id'] ?? null;

    if ($action === 'delete' && $partnerId) {
      $token = $_GET['token'] ?? '';
      if (!\App\Core\Csrf::checkToken($token)) {
        $_SESSION['flash_error'] = 'Erreur CSRF - opération annulée';
      } else {
        $partnerModel = new \App\Models\PartnerModel();
        $partnerModel->delete($partnerId);
        $_SESSION['flash'] = 'Partenaire supprimé avec succès';
      }
    }

    header('Location: ?page=admin&section=partners');
    exit;
  }

  protected function exportMessagesCsv()
  {
    if (session_status() === PHP_SESSION_NONE)
      session_start();
    $this->requireRole(['admin', 'manager']);

    $q = trim($_GET['q'] ?? '');

    $msgModel = new MessageModel();
    // get all matching (no pagination for export)
    $res = $msgModel->getPaginated(1, 1000000, $q);
    $items = $res['items'];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="messages_export_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Subject', 'Message', 'IP', 'Created At']);
    foreach ($items as $m) {
      $id = (string) ($m['_id'] ?? '');
      $dt = isset($m['created_at']) ? $m['created_at']->toDateTime()->format('Y-m-d H:i:s') : '';
      fputcsv($out, [$id, $m['name'] ?? '', $m['email'] ?? '', $m['subject'] ?? '', $m['message'] ?? '', $m['ip'] ?? '', $dt]);
    }
    fclose($out);
    exit;
  }
}
