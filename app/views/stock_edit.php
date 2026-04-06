<?php
if (session_status() === PHP_SESSION_NONE) {
    // Session started in front controller (public/index.php)
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php $title = 'Modifier - Stock';
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }
        
        .readonly-field {
            background: #f9fafb;
            color: #6b7280;
        }
        
        .operation-toggle {
            display: flex;
            gap: 12px;
        }
        
        .operation-option {
            flex: 1;
            position: relative;
        }
        
        .operation-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .operation-label {
            display: block;
            padding: 14px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .operation-option input[type="radio"]:checked + .operation-label {
            background: #4facfe;
            color: white;
            border-color: #4facfe;
        }
        
        .operation-label:hover {
            border-color: #4facfe;
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
        
        .btn-action-group {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            padding: 0 28px 28px;
        }
        
        .btn-primary-custom {
            flex: 1;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.3);
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
        <div class="edit-card">
            <div class="card-header-custom">
                <h2>
                    <i class="bi bi-box-seam"></i>
                    Modifier l'opération de stock
                </h2>
            </div>
            
            <form method="post" action="?page=stock&action=edit&id=<?= urlencode($entry['_id']) ?>" id="editStockForm">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
                <input type="hidden" name="id" value="<?= urlencode($entry['_id']) ?>">
                
                <!-- Zone d'alertes pour messages d'erreur/succès -->
                <div id="editStockAlert" style="display:none; margin: 24px; margin-bottom: 1rem;">
                  <div id="editStockAlertContent"></div>
                </div>
                
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
                            <div class="operation-toggle">
                                <div class="operation-option">
                                    <input type="radio" name="operation" id="operation_entree" value="entree" 
                                        <?= ($entry['entree']['qte'] ?? 0) > 0 ? 'checked' : '' ?> required>
                                    <label class="operation-label" for="operation_entree">
                                        <i class="bi bi-arrow-down-circle"></i> Entrée
                                    </label>
                                </div>
                                <div class="operation-option">
                                    <input type="radio" name="operation" id="operation_sortie" value="sortie" 
                                        <?= ($entry['sortie']['qte'] ?? 0) > 0 ? 'checked' : '' ?> required>
                                    <label class="operation-label" for="operation_sortie">
                                        <i class="bi bi-arrow-up-circle"></i> Sortie
                                    </label>
                                </div>
                            </div>
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
                        </div>
                        
                        <div class="col-12">
                            <label for="intitule" class="form-label">
                                <i class="bi bi-tag"></i> Intitulé compte
                            </label>
                            <input type="text" name="intitule" id="intitule" class="form-control readonly-field"
                                value="<?= htmlspecialchars($entry['intitule'] ?? '') ?>" required readonly>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="lieu" class="form-label">
                                <i class="bi bi-geo-alt"></i> Lieu
                            </label>
                            <input type="text" name="lieu" id="lieu" class="form-control"
                                value="<?= htmlspecialchars($entry['lieu'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="designation" class="form-label">
                                <i class="bi bi-card-text"></i> Désignation
                            </label>
                            <input type="text" name="designation" id="designation" class="form-control"
                                value="<?= htmlspecialchars($entry['designation'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="quantite" class="form-label">
                                <i class="bi bi-box"></i> Quantité
                            </label>
                            <input type="number" step="0.01" name="quantite" id="quantite" class="form-control"
                                value="<?= ($entry['entree']['qte'] ?? $entry['sortie']['qte'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="pu" class="form-label">
                                <i class="bi bi-currency-dollar"></i> Prix Unitaire
                            </label>
                            <input type="number" step="0.01" name="pu" id="pu" class="form-control"
                                value="<?= ($entry['entree']['pu'] ?? $entry['sortie']['pu'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="btn-action-group">
                    <button type="submit" class="btn-primary-custom" id="editStockSubmitBtn">
                        <i class="bi bi-check-circle"></i> Enregistrer
                    </button>
                    <button type="button" class="btn-primary-custom" id="editStockReloadBtn" style="display:none;">
                        <i class="bi bi-arrow-clockwise"></i> Recharger
                    </button>
                    <a href="?page=stock" class="btn-secondary-custom">
                        <i class="bi bi-x-circle"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const currentCompte = "<?= htmlspecialchars($entry['compte'] ?? '') ?>";

        // Use shared AccountSearch and pre-fill if possible
        document.addEventListener('DOMContentLoaded', function () {
            AccountSearch.fetchComptes().then(function () {
                AccountSearch.createSuggestionBox({
                    inputId: 'compte_search',
                    suggestionsId: 'compte_suggestions',
                    renderItemHtml: function (c) {
                        return `<div class="suggestion-item">
                                <div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>
                                <div class="btn-group btn-group-sm mt-2" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="choose">
                                        <i class="bi bi-check-circle"></i> Choisir
                                    </button>
                                </div>
                            </div>`;
                    },
                    onChoose: function (item) {
                        if (!item) return;
                        document.getElementById('compte').value = item.code;
                        document.getElementById('compte_display').value = item.code + ' — ' + (item.label || '');
                        document.getElementById('intitule').value = item.intitule || '';
                    }
                });

                // Pre-fill currentCompte if available
                if (currentCompte) {
                    const acc = (window.comptesList || []).find(c => c.code === currentCompte);
                    if (acc) {
                        document.getElementById('compte').value = acc.code;
                        document.getElementById('intitule').value = acc.intitule || '';
                        document.getElementById('compte_display').value = acc.code + ' — ' + (acc.label || '');
                    }
                }
            }).catch(console.error);
        });

        // Helpers
        function escapeHtml(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
        function escapeAttr(s) { return String(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

        function updateIntitule() {
            const intituleInput = document.getElementById('intitule');
            const compteEl = document.getElementById('compte');
            if (!compteEl || !intituleInput) return;
            // If the element is a select (legacy), read dataset on selected option
            if (compteEl.tagName && compteEl.tagName.toLowerCase() === 'select') {
                const selected = compteEl.options[compteEl.selectedIndex];
                if (selected && selected.value && selected.dataset.intitule) {
                    intituleInput.value = selected.dataset.intitule;
                    return;
                }
            }
            // Otherwise, lookup by code in the loaded comptes list
            const code = compteEl.value;
            if (code) {
                const acc = (window.comptesList || []).find(c => c.code === code);
                intituleInput.value = acc ? (acc.intitule || '') : '';
            } else {
                intituleInput.value = '';
            }
        }

        // Gestion de la soumission AJAX du formulaire d'édition
        const editStockForm = document.getElementById('editStockForm');
        const editStockAlert = document.getElementById('editStockAlert');
        const editStockAlertContent = document.getElementById('editStockAlertContent');
        const editStockSubmitBtn = document.getElementById('editStockSubmitBtn');
        const editStockReloadBtn = document.getElementById('editStockReloadBtn');

        function showEditAlert(message, type = 'danger') {
          const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
          editStockAlertContent.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
              ${message}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          `;
          editStockAlert.style.display = 'block';
        }

        function hideEditAlert() {
          editStockAlert.style.display = 'none';
          editStockAlertContent.innerHTML = '';
        }

        if (editStockForm) {
          editStockForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            hideEditAlert();
            
            const quantiteInput = document.getElementById('quantite');
            const q = parseFloat(quantiteInput.value || '0');
            
            // Validation côté client
            if (!q || q <= 0) {
              showEditAlert('Quantité invalide', 'danger');
              return false;
            }

            // Soumettre via AJAX
            const formData = new FormData(editStockForm);
            
            try {
              editStockSubmitBtn.disabled = true;
              const response = await fetch(editStockForm.action, {
                method: 'POST',
                body: formData
              });

              const data = await response.json();

              if (data.success) {
                showEditAlert(data.message || 'Opération modifiée avec succès', 'success');
                // Masquer le bouton Enregistrer et afficher le bouton Recharger
                editStockSubmitBtn.style.display = 'none';
                editStockReloadBtn.style.display = 'block';
              } else {
                showEditAlert(data.error || 'Une erreur est survenue', 'danger');
                editStockSubmitBtn.disabled = false;
              }
            } catch (error) {
              console.error('Erreur lors de la soumission:', error);
              showEditAlert('Erreur réseau: ' + error.message, 'danger');
              editStockSubmitBtn.disabled = false;
            }
          });

          // Bouton recharger
          if (editStockReloadBtn) {
            editStockReloadBtn.addEventListener('click', function() {
              location.reload();
            });
          }
        }

    </script>
    <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>