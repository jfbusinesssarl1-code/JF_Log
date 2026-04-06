<?php
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Modifier - Caisse';
  require __DIR__ . '/_layout_head.php'; ?>
  <style>
    .edit-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .edit-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      margin-bottom: 30px;
    }
    
    .card-header-custom {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
      padding: 24px;
      border-bottom: none;
    }
    
    .card-header-custom h2 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .form-section {
      padding: 28px;
    }
    
    .form-label {
      font-weight: 600;
      color: #374151;
      margin-bottom: 8px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .form-control, .form-select {
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 15px;
      transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #f5576c;
      box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.1);
    }
    
    .type-toggle {
      display: flex;
      gap: 12px;
      margin-bottom: 20px;
    }
    
    .type-option {
      flex: 1;
      position: relative;
    }
    
    .type-option input[type="radio"] {
      position: absolute;
      opacity: 0;
    }
    
    .type-label {
      display: block;
      padding: 14px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 600;
    }
    
    .type-option input[type="radio"]:checked + .type-label {
      background: #f5576c;
      color: white;
      border-color: #f5576c;
    }
    
    .type-label.entree:not(:has(input:checked)) {
      border-color: #10b981;
      color: #10b981;
    }
    
    .type-label.sortie:not(:has(input:checked)) {
      border-color: #ef4444;
      color: #ef4444;
    }
    
    .type-label:hover {
      border-color: #f5576c;
    }
    
    .btn-action-group {
      display: flex;
      gap: 12px;
      margin-top: 28px;
      padding: 0 28px 28px;
    }
    
    .btn-primary-custom {
      flex: 1;
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      border: none;
      color: white;
      padding: 14px 24px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(245, 87, 108, 0.3);
    }
    
    .btn-secondary-custom {
      flex: 1;
      background: #f3f4f6;
      border: 2px solid #e5e7eb;
      color: #374151;
      padding: 14px 24px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 16px;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .btn-secondary-custom:hover {
      background: #e5e7eb;
      color: #1f2937;
    }
    
    .amount-display {
      text-align: right;
      font-size: 18px;
      font-weight: 700;
      color: #374151;
    }
    
    @media (max-width: 768px) {
      .edit-container {
        padding: 12px;
      }
      
      .form-section {
        padding: 20px;
      }
      
      .btn-action-group {
        flex-direction: column;
        padding: 0 20px 20px;
      }
      
      .card-header-custom h2 {
        font-size: 20px;
      }
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  
  <div class="edit-container">
    <?php if (!empty($entry)): ?>
    <div class="edit-card">
      <div class="card-header-custom">
        <h2>
          <i class="bi bi-wallet2"></i>
          Modifier l'opération de caisse
        </h2>
      </div>
      
      <form method="post" action="?page=caisse&action=edit&id=<?= $entry['_id'] ?? '' ?>">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
        
        <div class="form-section">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="date" class="form-label">
                <i class="bi bi-calendar3"></i> Date
              </label>
              <input type="date" name="date" id="date" class="form-control" 
                value="<?= htmlspecialchars($entry['date'] ?? '') ?>" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">
                <i class="bi bi-arrow-left-right"></i> Type d'opération
              </label>
              <div class="type-toggle">
                <div class="type-option">
                  <input type="radio" name="type" id="type_entree" value="entree" 
                    <?= ($entry['type'] ?? '') === 'entree' ? 'checked' : '' ?> required>
                  <label class="type-label entree" for="type_entree">
                    <i class="bi bi-arrow-down-circle"></i> Entrée
                  </label>
                </div>
                <div class="type-option">
                  <input type="radio" name="type" id="type_sortie" value="sortie" 
                    <?= ($entry['type'] ?? '') === 'sortie' ? 'checked' : '' ?> required>
                  <label class="type-label sortie" for="type_sortie">
                    <i class="bi bi-arrow-up-circle"></i> Sortie
                  </label>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <label for="numero_bon_manuscrit" class="form-label">
                <i class="bi bi-file-text"></i> N° Bon Manuscrit
              </label>
              <input type="text" name="numero_bon_manuscrit" id="numero_bon_manuscrit" class="form-control"
                value="<?= htmlspecialchars($entry['numero_bon_manuscrit'] ?? '') ?>" required>
            </div>
            
            <div class="col-md-6">
              <label for="operateur" class="form-label">
                <i class="bi bi-person"></i> Opérateur
              </label>
              <input type="text" name="operateur" id="operateur" class="form-control"
                value="<?= htmlspecialchars($entry['operateur'] ?? '') ?>" required>
            </div>
            
            <div class="col-12">
              <label for="libelle" class="form-label">
                <i class="bi bi-tags"></i> Libellé
              </label>
              <input type="text" name="libelle" id="libelle" class="form-control"
                value="<?= htmlspecialchars($entry['libelle'] ?? '') ?>" required>
            </div>
            
            <div class="col-12">
              <label for="montant" class="form-label">
                <i class="bi bi-cash-stack"></i> Montant
              </label>
              <input type="number" step="0.01" name="montant" id="montant" 
                class="form-control amount-display"
                value="<?= htmlspecialchars($entry['montant'] ?? '') ?>" required>
            </div>
          </div>
        </div>
        
        <div class="btn-action-group">
          <button type="submit" class="btn-primary-custom">
            <i class="bi bi-check-circle"></i> Enregistrer
          </button>
          <a href="?page=caisse" class="btn-secondary-custom">
            <i class="bi bi-x-circle"></i> Annuler
          </a>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle"></i> Opération introuvable
    </div>
    <?php endif; ?>
  </div>
  
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>