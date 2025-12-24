<?php
/**
 * Script d'initialisation du système
 * Crée le premier utilisateur administrateur
 * 
 * À exécuter une seule fois après la première installation
 * Puis supprimer ce fichier ou le protéger
 */

// Configuration
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // ⚠️ À CHANGER après la première connexion

try {
  // Charger l'autoloader
  require_once __DIR__ . '/vendor/autoload.php';

  echo "\n=== Initialisation du système CB.JF ===\n\n";

  // Créer l'utilisateur admin
  $userModel = new \App\Models\UserModel();

  // Vérifier si l'admin existe déjà
  $existing = $userModel->findByUsername(ADMIN_USERNAME);
  if ($existing) {
    echo "✓ L'utilisateur admin existe déjà.\n";
    echo "  Nom d'utilisateur: " . htmlspecialchars($existing['username']) . "\n";
    echo "  Rôle: " . htmlspecialchars($existing['role'] ?? 'unknown') . "\n";
    exit(0);
  }

  // Créer le nouvel admin
  $result = $userModel->create(ADMIN_USERNAME, ADMIN_PASSWORD, 'admin');

  echo "✓ Utilisateur administrateur créé avec succès!\n\n";
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
  echo "📋 INFORMATIONS DE CONNEXION\n";
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
  echo "Nom d'utilisateur: " . ADMIN_USERNAME . "\n";
  echo "Mot de passe: " . ADMIN_PASSWORD . "\n";
  echo "Rôle: admin\n";
  echo "\n⚠️  ACTIONS IMPORTANTES:\n";
  echo "1. ✓ Accédez à http://localhost/CB.JF/public/\n";
  echo "2. ✓ Connectez-vous avec les identifiants ci-dessus\n";
  echo "3. ✓ Allez dans 'Gestion Utilisateurs' pour créer d'autres comptes\n";
  echo "4. ✓ CHANGEZ le mot de passe de l'admin immédiatement\n";
  echo "5. ✓ Supprimez ce fichier (init.php) après la première utilisation\n";
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

} catch (Exception $e) {
  echo "✗ Erreur: " . $e->getMessage() . "\n";
  echo "Trace: " . $e->getTraceAsString() . "\n";
  exit(1);
}

