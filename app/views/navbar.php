<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
}

// Récupérer la page active depuis l'URL
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top shadow-sm">
  <div class="container-fluid navbar-container">
    <!-- Logo à gauche -->
    <a class="navbar-brand navbar-brand-custom order-lg-1 d-flex flex-column align-items-center" href="?page=dashboard">
      <img id="logoPreviewImg" src="/asset.php?f=images/logo.png" alt="Logo preview">
      <div class="d-none d-md-flex ms-2 align-items-center justify-content-center">
        <div class="h4 mb-0 fw-bold" style="margin-right: 10px;">CB.JF</div>
        <div class="small text-light">Comptabilité</div>
      </div>
    </a>

    <!-- Bouton toggle mobile -->
    <button class="navbar-toggler order-lg-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu centré -->
    <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
      <?php $__role = $_SESSION['user']['role'] ?? 'guest'; ?>
      <ul class="navbar-nav mx-auto">
        <?php if ($__role === 'caissier'): ?>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'caisse') ? 'active' : '' ?>"
              href="?page=caisse">Caisse</a></li>
        <?php elseif ($__role === 'stock_manager'): ?>
          <!-- Menu limité pour Stock Manager: Dashboard et Stock uniquement -->
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>"
              href="?page=dashboard">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'stock') ? 'active' : '' ?>"
              href="?page=stock">Stock</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>"
              href="?page=dashboard">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'journal') ? 'active' : '' ?>"
              href="?page=journal">Journal</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'grandlivre') ? 'active' : '' ?>"
              href="?page=grandlivre">Gd-Livre</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'balance') ? 'active' : '' ?>"
              href="?page=balance">Balance</a></li>
          <?php if (in_array($__role, ['accountant', 'manager', 'admin', 'comptable'])): ?>
            <li class="nav-item"><a class="nav-link <?= ($currentPage === 'bilan') ? 'active' : '' ?>"
                href="?page=bilan">Bilan</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'stock') ? 'active' : '' ?>"
              href="?page=stock">Stock</a></li>
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'releve') ? 'active' : '' ?>"
              href="?page=releve">Relevé</a></li>
          <?php if (in_array($__role, ['caissier', 'accountant', 'manager', 'admin', 'comptable'])): ?>
            <li class="nav-item"><a class="nav-link <?= ($currentPage === 'caisse') ? 'active' : '' ?>"
                href="?page=caisse">Caisse</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
    </div>

    <!-- Utilisateur à droite avec menu dropdown -->
    <ul class="navbar-nav order-lg-4 ms-auto align-items-center navbar-user">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle user-dropdown" href="#" id="userDropdown" role="button"
          data-bs-toggle="dropdown" aria-expanded="false">
          <span class="d-none d-md-inline">👤
            <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?></span>
          <span class="d-md-none">👤</span>
          <span class="badge bg-accent ms-2"><?php //htmlspecialchars($_SESSION['user']['role'] ?? 'user') ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-logout" aria-labelledby="userDropdown">
          <li class="dropdown-item-text px-3 py-2">
            <div class="small"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?></div>
            <div class="small text-info">
              <?= htmlspecialchars($_SESSION['user']['role'] ?? 'user') ?>
            </div>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <?php if (in_array($__role, ['admin', 'manager'])): ?>
            <li><a class="dropdown-item" href="?page=admin">
                <i class="bi bi-gear"></i> ⚙️ Paramètres
              </a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
          <?php endif; ?>
          <?php if ($__role === 'admin'): ?>
            <li><a class="dropdown-item" href="?page=payroll">
                <i class="fas fa-file-invoice-dollar"></i> 📋 Gestion de Paie
              </a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
          <?php endif; ?>
          <li><a class="dropdown-item text-danger" href="?page=logout">
              <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a></li>
        </ul>
      </li>
    </ul>

  </div>
</nav>

<!-- Spacer pour le contenu (compensé pour navbar fixed) -->
<div class="navbar-spacer"></div>