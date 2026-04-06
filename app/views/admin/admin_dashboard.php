<?php
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
}

// Vérifier que l'utilisateur est admin ou manager
$userRole = $_SESSION['user']['role'] ?? 'guest';
if (!in_array($userRole, ['admin', 'manager'])) {
  header('Location: ?page=dashboard');
  exit;
}

$currentSection = $currentSection ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administration | CB.JF</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/asset.php?f=css/custom.css">
  <style>
  :root {
    --sidebar-width: 280px;
    --admin-navbar-height: 60px;
    --primary-color: #1a5490;
    --sidebar-bg: #f8f9fa;
    --text-color: #333;
  }

  body {
    padding-top: var(--admin-navbar-height);
    margin-left: var(--sidebar-width);
    background-color: #f5f5f5;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  /* SIDEBAR STYLES */
  .admin-sidebar {
    position: fixed;
    left: 0;
    top: var(--admin-navbar-height);
    width: var(--sidebar-width);
    height: calc(100vh - var(--admin-navbar-height));
    background: linear-gradient(135deg, var(--sidebar-bg) 0%, #e9ecef 100%);
    border-right: 1px solid #dee2e6;
    overflow-y: auto;
    padding: 20px 0;
    z-index: 100;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
  }

  .sidebar-section {
    margin-bottom: 30px;
  }

  .sidebar-section-title {
    padding: 10px 20px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--primary-color);
    letter-spacing: 0.5px;
  }

  .sidebar-menu-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    margin: 0 10px;
    color: var(--text-color);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 0.95rem;
  }

  .sidebar-menu-item:hover {
    background-color: rgba(26, 84, 144, 0.1);
    color: var(--primary-color);
    transform: translateX(5px);
  }

  .sidebar-menu-item.active {
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
  }

  .sidebar-menu-item i {
    width: 24px;
    margin-right: 12px;
  }

  /* ADMIN NAVBAR STYLES */
  .admin-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--admin-navbar-height);
    background: linear-gradient(135deg, #0a1628 0%, #1a2332 100%);
    display: flex;
    align-items: center;
    padding: 0 30px;
    z-index: 200;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
  }

  .admin-navbar-brand {
    color: #ffffff !important;
    font-size: 1.5rem;
    font-weight: 700;
    margin-right: auto;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none !important;
    transition: all 0.3s ease;
  }

  .admin-navbar-brand:hover {
    color: #87CEEB !important;
  }

  .admin-navbar-brand i {
    font-size: 1.8rem;
    color: #87CEEB;
    background: linear-gradient(135deg, rgba(135, 206, 235, 0.2), rgba(79, 195, 247, 0.3));
    padding: 12px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(135, 206, 235, 0.4);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .admin-navbar-brand:hover i {
    color: #ffffff;
    background: linear-gradient(135deg, #87CEEB, #4FC3F7);
    transform: rotate(-5deg) scale(1.1);
    box-shadow: 0 6px 20px rgba(135, 206, 235, 0.6);
  }

  .admin-navbar-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 30px;
    align-items: center;
  }

  .admin-navbar-menu li a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.95rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    position: relative;
  }

  .admin-navbar-menu li a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    width: 80%;
    height: 3px;
    background: #87CEEB;
    border-radius: 2px;
    transition: transform 0.3s ease;
  }

  .admin-navbar-menu li a:hover,
  .admin-navbar-menu li a.active {
    color: #87CEEB !important;
    font-weight: 600;
    background: rgba(135, 206, 235, 0.1);
  }

  .admin-navbar-menu li a:hover::after,
  .admin-navbar-menu li a.active::after {
    transform: translateX(-50%) scaleX(1);
  }

  .admin-navbar-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 20px;
  }

  .btn-logout {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.2), rgba(220, 53, 69, 0.3));
    color: white;
    border: 2px solid rgba(220, 53, 69, 0.5);
    padding: 8px 20px;
    border-radius: 50px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(220, 53, 69, 0.2);
  }

  .btn-logout:hover {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.8), rgb(220, 53, 69));
    border-color: rgba(255, 255, 255, 0.8);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    color: white;
  }

  /* MAIN CONTENT STYLES */
  .admin-main-content {
    padding: 30px;
    margin-left: 0;
  }

  .section-header {
    margin-bottom: 30px;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 15px;
  }

  .section-header h1 {
    color: var(--primary-color);
    margin: 0;
    font-weight: 700;
  }

  .section-header p {
    color: #666;
    margin: 5px 0 0 0;
    font-size: 0.95rem;
  }

  /* RESPONSIVE DESIGN */
  @media (max-width: 768px) {
    :root {
      --sidebar-width: 0;
    }

    body {
      margin-left: 0;
    }

    .admin-sidebar {
      left: -var(--sidebar-width);
      transition: left 0.3s ease;
    }

    .admin-sidebar.show {
      left: 0;
    }

    .admin-navbar-menu {
      display: none;
    }

    .admin-navbar-menu.show {
      display: flex;
      position: absolute;
      top: var(--admin-navbar-height);
      left: 0;
      right: 0;
      flex-direction: column;
      background-color: rgba(13, 59, 122, 0.95);
      padding: 20px;
      gap: 10px;
    }

    .admin-main-content {
      padding: 20px;
    }
  }

  /* SCROLLBAR STYLING */
  .admin-sidebar::-webkit-scrollbar {
    width: 6px;
  }

  .admin-sidebar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
  }

  .admin-sidebar::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
  }

  /* CONTENT CARDS */
  .admin-card {
    background: white;
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
  }

  .admin-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  }
  </style>
</head>

<body>
  <!-- ADMIN NAVBAR -->
  <nav class="admin-navbar">
    <a href="?page=admin&section=dashboard" class="admin-navbar-brand" style="text-decoration: none; color: white; cursor: pointer;">
      <i class="bi bi-gear-fill"></i>
      Administration CB.JF
    </a>

    <button class="btn btn-light d-md-none" id="sidebarToggle" style="margin-right: 15px;">
      <i class="bi bi-list"></i>
    </button>

    <ul class="admin-navbar-menu" style="display: none;">
      <li><a href="?page=admin&section=dashboard" class="<?= ($currentSection === 'dashboard') ? 'active' : '' ?>">
          <i class="bi bi-house"></i> Accueil
        </a></li>
      <li><a href="?page=admin&section=about" class="<?= ($currentSection === 'about') ? 'active' : '' ?>">
          <i class="bi bi-info-circle"></i> À propos
        </a></li>
      <li><a href="?page=admin&section=services" class="<?= ($currentSection === 'services') ? 'active' : '' ?>">
          <i class="bi bi-briefcase"></i> Services
        </a></li>
      <li><a href="?page=admin&section=activities" class="<?= ($currentSection === 'activities') ? 'active' : '' ?>">
          <i class="bi bi-lightning"></i> Activités
        </a></li>
      <li><a href="?page=admin&section=contact" class="<?= ($currentSection === 'contact') ? 'active' : '' ?>">
          <i class="bi bi-telephone"></i> Contact
        </a></li>
    </ul>

    <div class="admin-navbar-right">
      <span class="text-white small">
        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?>
      </span>
      <a href="?page=logout" class="btn-logout">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
      </a>
    </div>
  </nav>

  <!-- SIDEBAR -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-section">
      <div class="sidebar-section-title"><i class="bi bi-pencil-square"></i> Contenu</div>
      <a href="?page=admin&section=home" class="sidebar-menu-item <?= ($currentSection === 'home') ? 'active' : '' ?>">
        <i class="bi bi-images"></i>
        <span>Section Accueil</span>
      </a>
      <a href="?page=admin&section=about"
        class="sidebar-menu-item <?= ($currentSection === 'about') ? 'active' : '' ?>">
        <i class="bi bi-file-text"></i>
        <span>Section À propos</span>
      </a>
      <a href="?page=admin&section=services"
        class="sidebar-menu-item <?= ($currentSection === 'services') ? 'active' : '' ?>">
        <i class="bi bi-star"></i>
        <span>Section Services</span>
      </a>
      <a href="?page=admin&section=activities"
        class="sidebar-menu-item <?= ($currentSection === 'activities') ? 'active' : '' ?>">
        <i class="bi bi-clock-history"></i>
        <span>Activités en cours</span>
      </a>
      <a href="?page=admin&section=partners"
        class="sidebar-menu-item <?= ($currentSection === 'partners') ? 'active' : '' ?>">
        <i class="bi bi-handshake"></i>
        <span>Nos partenaires</span>
      </a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-title"><i class="bi bi-chat"></i> Communication</div>
      <a href="?page=admin&section=messages"
        class="sidebar-menu-item <?= ($currentSection === 'messages') ? 'active' : '' ?>">
        <i class="bi bi-chat-dots"></i>
        <span>Messages</span>
      </a>
    </div>

    <?php if ($userRole === 'admin'): ?>
    <div class="sidebar-section">
      <div class="sidebar-section-title"><i class="bi bi-shield-lock"></i> Administration</div>
      <a href="?page=admin&section=users"
        class="sidebar-menu-item <?= ($currentSection === 'users') ? 'active' : '' ?>">
        <i class="bi bi-people"></i>
        <span>Gestion Utilisateurs</span>
      </a>
    </div>
    <?php endif; ?>

    <div class="sidebar-section">
      <div class="sidebar-section-title"><i class="bi bi-arrow-left"></i> Navigation</div>
      <a href="?page=dashboard" class="sidebar-menu-item">
        <i class="bi bi-graph-up"></i>
        <span>Retour au Dashboard</span>
      </a>
    </div>
  </aside>

  <!-- FLASH MESSAGE -->
  <?php if (!empty($_SESSION['flash'])): ?>
  <div class="container" style="margin-top:20px; margin-left: 20px;">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['flash']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <!-- MAIN CONTENT -->
  <main class="admin-main-content">
    <!-- Contenu des sections sera chargé ici via les sous-vues -->
    <?php
    // Déterminer quelle section afficher
    switch ($currentSection) {
      case 'home':
        include __DIR__ . '/sections/home_section.php';
        break;
      case 'about':
        include __DIR__ . '/sections/about_section.php';
        break;
      case 'services':
        include __DIR__ . '/sections/services_section.php';
        break;
      case 'activities':
        include __DIR__ . '/sections/activities_section.php';
        break;
      case 'partners':
        include __DIR__ . '/sections/partners_section.php';
        break;
      case 'messages':
        include __DIR__ . '/sections/messages_section.php';
        break;
      case 'users':
        include __DIR__ . '/sections/users_section.php';
        break;
      default:
        include __DIR__ . '/sections/dashboard_section.php';
    }
    ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Toggle sidebar on mobile
  document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    const sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('show');
  });

  // Close sidebar when clicking on a menu item (mobile)
  document.querySelectorAll('.sidebar-menu-item').forEach(item => {
    item.addEventListener('click', function() {
      if (window.innerWidth < 768) {
        document.getElementById('adminSidebar').classList.remove('show');
      }
    });
  });
  </script>
</body>

</html>