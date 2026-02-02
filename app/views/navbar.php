<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// DEBUG: Log session user data
error_log('DEBUG navbar - SESSION user: ' . json_encode($_SESSION['user'] ?? 'NOT SET'));

$currentPage = basename($_SERVER['REQUEST_URI']);

// echo ($currentPage)
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
  <div class="container">

    <a class="navbar-brand d-flex align-items-center gap-2" href="?page=dashboard">
      <img id="logoPreviewImg" src="/asset.php?f=images/logo.png" alt="Logo preview">
      <div class="d-none d-md-block">
        <div class="h6 mb-0 fw-bold">CB.JF</div>
        <div class="small text-muted">Comptabilité</div>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="#navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= ($currentPage == '?page=journal') ? 'active' : '' ?>"
            href="?page=journal">Journal</a></li>
        <li class="nav-item"><a class="nav-link <?= ($currentPage == '?page=grandlivre') ? 'active' : '' ?>"
            href="?page=grandlivre">Grand-Livre</a></li>
        <li class="nav-item"><a class="nav-link <?= ($currentPage == '?page=balance') ? 'active' : '' ?>"
            href="?page=balance">Balance</a></li>
        <li class="nav-item"><a class="nav-link <?= ($currentPage == '?page=stock') ? 'active' : '' ?>"
            href="?page=stock">Fiche de Stock</a></li>
        <li class="nav-item"><a class="nav-link <?= ($currentPage == '?page=releve') ? 'active' : '' ?>"
            href="?page=releve">Relevé</a></li>
        <?php if (isset($_SESSION['user']['role']) && in_array(needle: $_SESSION['user']['role'], haystack: ['caissier', 'manager', 'admin', 'comptable'])): ?>
          <li class="nav-item"><a class="nav-link <?= ($currentPage == '?page=caisse') ? 'active' : '' ?>"
              href="?page=caisse">Caisse</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
          <li class="nav-item"><a
              class="nav-link text-info fw-bold <?= ($currentPage == '?page=register') ? 'active' : '' ?>"
              href="?page=register">⚙️ Gestion Utilisateurs</a></li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-2 d-none d-md-block"><span class="small text-muted">👤
            <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Utilisateur') ?>
            (<?= htmlspecialchars($_SESSION['user']['role'] ?? 'user') ?>)</span></li>
        <li class="nav-item"><a class="nav-link text-danger fw-bold" href="?page=logout">Déconnexion</a></li>
      </ul>
    </div>

  </div>
</nav>