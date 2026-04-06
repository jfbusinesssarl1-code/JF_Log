<?php
if (session_status() === PHP_SESSION_NONE) {
  // Session started in front controller (public/index.php)
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Modifier - Journal';
  require __DIR__ . '/_layout_head.php'; ?>
  <style>
    .edit-container {
      max-width: 900px;
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
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    }
    
    .form-control, .form-select {
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 15px;
      transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .side-selector {
      display: flex;
      gap: 12px;
      margin-top: 12px;
    }
    
    .side-option {
      flex: 1;
      position: relative;
    }
    
    .side-option input[type="radio"] {
      position: absolute;
      opacity: 0;
    }
    
    .side-label {
      display: block;
      padding: 12px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 600;
    }
    
    .side-option input[type="radio"]:checked + .side-label {
      background: #667eea;
      color: white;
      border-color: #667eea;
    }
    
    .side-label:hover {
      border-color: #667eea;
    }
    
    .btn-action-group {
      display: flex;
      gap: 12px;
      margin-top: 28px;
      padding: 0 28px 28px;
    }
    
    .btn-primary-custom {
      flex: 1;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
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
    
    .readonly-field {
      background: #f9fafb;
      color: #6b7280;
    }
    
    .suggestions-dropdown {
      background: white;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      margin-top: 8px;
      max-height: 300px;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .suggestion-item {
      padding: 12px 16px;
      cursor: pointer;
      border-bottom: 1px solid #f3f4f6;
      transition: background 0.2s ease;
    }
    
    .suggestion-item:hover {
      background: #f9fafb;
    }
    
    .suggestion-item:last-child {
      border-bottom: none;
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
    <?php if ($entry): ?>
    <div class="edit-card">
      <div class="card-header-custom">
        <h2>
          <i class="bi bi-pencil-square"></i>
          Modifier l'opération
        </h2>
      </div>
      
      <form method="post">
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
              <label for="lieu" class="form-label">
                <i class="bi bi-geo-alt"></i> Lieu
              </label>
              <input type="text" name="lieu" id="lieu" class="form-control"
                value="<?= htmlspecialchars($entry['lieu'] ?? '') ?>" required>
            </div>
            
            <div class="col-12">
              <label for="compte_search" class="form-label">
                <i class="bi bi-search"></i> Rechercher un compte
              </label>
              <input type="text" id="compte_search" class="form-control"
                placeholder="Tapez le code ou le libellé du compte...">
              <div id="compte_suggestions" class="suggestions-dropdown" style="display:none;"></div>
              <input type="hidden" name="compte" id="compte">
              <input type="text" id="compte_display" class="form-control mt-2 readonly-field" 
                placeholder="Compte sélectionné" readonly>
              
              <div class="side-selector">
                <div class="side-option">
                  <input type="radio" name="side" id="side_debit" value="debit">
                  <label class="side-label" for="side_debit">
                    <i class="bi bi-arrow-down-circle"></i> Débit
                  </label>
                </div>
                <div class="side-option">
                  <input type="radio" name="side" id="side_credit" value="credit">
                  <label class="side-label" for="side_credit">
                    <i class="bi bi-arrow-up-circle"></i> Crédit
                  </label>
                </div>
              </div>
            </div>
            
            <div class="col-12">
              <label for="libelle" class="form-label">
                <i class="bi bi-tags"></i> Libellé
              </label>
              <input type="text" name="libelle" id="libelle" class="form-control"
                value="<?= htmlspecialchars($entry['libelle'] ?? '') ?>" required>
            </div>
            
            <div class="col-md-6">
              <label for="quantite" class="form-label">
                <i class="bi bi-box"></i> Quantité
              </label>
              <input type="number" step="0.01" min="0.01" name="quantite" id="quantite" class="form-control"
                value="<?= htmlspecialchars($entry['quantite'] ?? '') ?>" required>
            </div>
            
            <div class="col-md-6">
              <label for="prix_unitaire" class="form-label">
                <i class="bi bi-currency-dollar"></i> Prix unitaire
              </label>
              <input type="number" step="0.01" min="0.01" name="prix_unitaire" id="prix_unitaire" class="form-control"
                value="<?= htmlspecialchars($entry['prix_unitaire'] ?? '') ?>" required>
            </div>
            
            <div class="col-md-6">
              <label for="debit" class="form-label">
                <i class="bi bi-arrow-down-circle-fill text-danger"></i> Débit
              </label>
              <input type="number" step="0.01" name="debit" id="debit" class="form-control readonly-field"
                value="<?= htmlspecialchars($entry['debit'] ?? '') ?>" readonly>
            </div>
            
            <div class="col-md-6">
              <label for="credit" class="form-label">
                <i class="bi bi-arrow-up-circle-fill text-success"></i> Crédit
              </label>
              <input type="number" step="0.01" name="credit" id="credit" class="form-control readonly-field"
                value="<?= htmlspecialchars($entry['credit'] ?? '') ?>" readonly>
            </div>
          </div>
        </div>
        
        <div class="btn-action-group">
          <button type="submit" class="btn-primary-custom">
            <i class="bi bi-check-circle"></i> Enregistrer
          </button>
          <a href="?page=journal" class="btn-secondary-custom">
            <i class="bi bi-x-circle"></i> Annuler
          </a>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div class="alert alert-danger">
      <i class="bi bi-exclamation-triangle"></i> Opération introuvable.
    </div>
    <?php endif; ?>
  </div>
  
  <script>
    const currentCompte = "<?= htmlspecialchars($entry['compte'] ?? '') ?>";

    // Use shared AccountSearch helper and add auto-detect for debit/credit side
    document.addEventListener('DOMContentLoaded', function() {
      AccountSearch.fetchComptes().then(function() {
        AccountSearch.createSuggestionBox({
          inputId: 'compte_search',
          suggestionsId: 'compte_suggestions',
          renderItemHtml: function(c) {
            return `<div class="suggestion-item">
                <div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>
                <div class="btn-group btn-group-sm mt-2" role="group">
                  <button type="button" class="btn btn-sm btn-outline-danger" data-action="debit">
                    <i class="bi bi-arrow-down-circle"></i> Débit
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-success" data-action="credit">
                    <i class="bi bi-arrow-up-circle"></i> Crédit
                  </button>
                </div>
              </div>`;
          },
          onChoose: function(item, extra) {
            const action = extra && extra.action;
            selectAccountForEdit(item.code, action);
          }
        });

        // Pre-select current account if present and show display
        if (currentCompte && document.getElementById('compte')) {
          document.getElementById('compte').value = currentCompte;
          var acc = (window.comptesList || []).find(c => c.code === currentCompte);
          if (acc) document.getElementById('compte_display').value = acc.code + ' — ' + (acc.label || '');
        }

        // auto-detect side from values
        const debitEl = document.getElementById('debit');
        const creditEl = document.getElementById('credit');
        const setSideFromValues = function() {
          const d = parseFloat(debitEl?.value || '0');
          const c = parseFloat(creditEl?.value || '0');
          if (d > 0) document.getElementById('side_debit').checked = true;
          else if (c > 0) document.getElementById('side_credit').checked = true;
        };
        setSideFromValues();
        if (debitEl) debitEl.addEventListener('input', setSideFromValues);
        if (creditEl) creditEl.addEventListener('input', setSideFromValues);

        const quantiteInput = document.getElementById('quantite');
        const prixUnitaireInput = document.getElementById('prix_unitaire');
        const updateTotals = function() {
          const q = parseFloat(quantiteInput?.value || '0');
          const pu = parseFloat(prixUnitaireInput?.value || '0');
          const total = (isNaN(q) || isNaN(pu)) ? 0 : (q * pu);
          const side = document.getElementById('side_debit')?.checked ? 'debit' : 'credit';
          if (debitEl) debitEl.value = side === 'debit' && total > 0 ? total.toFixed(2) : '0.00';
          if (creditEl) creditEl.value = side === 'credit' && total > 0 ? total.toFixed(2) : '0.00';
        };
        if (quantiteInput) quantiteInput.addEventListener('input', updateTotals);
        if (prixUnitaireInput) prixUnitaireInput.addEventListener('input', updateTotals);
        document.getElementById('side_debit')?.addEventListener('change', updateTotals);
        document.getElementById('side_credit')?.addEventListener('change', updateTotals);
        updateTotals();
      }).catch(console.error);
    });

    function selectAccountForEdit(code, side) {
      const acc = (window.comptesList || []).find(c => c.code === code);
      if (!acc) return;
      const hidden = document.getElementById('compte');
      const debitRadio = document.getElementById('side_debit');
      const creditRadio = document.getElementById('side_credit');
      if (hidden) hidden.value = code;
      if (document.getElementById('compte_display')) document.getElementById('compte_display').value = acc.code +
        ' — ' + (acc.label || '');
      if (acc.intitule) {
        const intituleInput = document.getElementById('libelle');
        if (intituleInput && !intituleInput.value) intituleInput.value = acc.intitule;
      }
      if (side === 'debit' && debitRadio) debitRadio.checked = true;
      if (side === 'credit' && creditRadio) creditRadio.checked = true;
    }
    </script>
    <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>