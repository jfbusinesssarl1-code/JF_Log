<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Journal - Comptabilité';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body style="position: relative;">
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Journal Comptable</h2>
    </div>
    <form class="row g-2 mb-3" method="get">
      <input type="hidden" name="page" value="journal">
      <div class="col-md-2">
        <div class="position-relative">
          <input type="text" id="filter_compte_display" class="form-control" placeholder="Compte" value="">
          <input type="hidden" id="filter_compte" name="compte"
            value="<?= htmlspecialchars($filters['compte'] ?? '') ?>">
          <div id="filter_compte_suggestions" class="list-group"
            style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;"></div>
        </div>
      </div>
      <div class="col-md-2">
        <input type="text" class="form-control" name="lieu" placeholder="Lieu"
          value="<?= htmlspecialchars($filters['lieu'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <input type="date" class="form-control" name="date_debut"
          value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <input type="date" class="form-control" name="date_fin"
          value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <button class="btn btn-secondary w-100" type="submit">Filtrer</button>
      </div>
      <div class="col-md-2">
        <a class="btn btn-outline-secondary w-100 mb-2" href="?page=journal">Afficher tout</a>
        <a class="btn btn-outline-secondary w-100 position-fixed" style="max-width:150px; bottom:80px; right: 2%;"
          href="?page=journal&action=export&format=pdf&compte=<?= urlencode($filters['compte'] ?? '') ?>&lieu=<?= urlencode($filters['lieu'] ?? '') ?>&date_debut=<?= urlencode($filters['date_debut'] ?? '') ?>&date_fin=<?= urlencode($filters['date_fin'] ?? '') ?>">Exporter
          PDF</a>
      </div>
    </form>

    <div class="table-responsive shadow-sm rounded-3" style="font-size: small;">
      <table class="table table-bordered align-middle mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Compte</th>
            <th>Lieu</th>
            <th style="width:25%">Libellé</th>
            <th>Débit</th>
            <th>Crédit</th>
            <th style="width:1%">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($entries)):
            foreach ($entries as $entry): ?>
          <tr>
            <td><?= htmlspecialchars($entry['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($entry['compte'] ?? '') ?></td>
            <td><?= htmlspecialchars($entry['lieu'] ?? '') ?></td>
            <td><?= htmlspecialchars($entry['libelle'] ?? '') ?></td>
            <td><?= ($entry['debit'] !== '' ? '$ ' . htmlspecialchars($entry['debit']) : '') ?></td>
            <td><?= ($entry['credit'] !== '' ? '$ ' . htmlspecialchars($entry['credit']) : '') ?></td>
            <td class="d-flex justify-content-center gap-1">
              <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['accountant', 'admin'])): ?>
              <a href="?page=journal&action=edit&id=<?= $entry['_id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
              <a href="?page=journal&action=delete&id=<?= $entry['_id'] ?>" class="btn btn-sm btn-danger"
                onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
              <?php else: ?>
              —
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- bouton pour afficher le modal -->
    <div class="fixed-action-btn no-print">
      <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['accountant', 'admin'])): ?>
      <button class="btn btn-primary d-none d-md-inline-flex" data-bs-toggle="modal" data-bs-target="#journalAddModal"
        title="Ajouter une écriture"
        style="font-weight: bold; font-size: large; position: fixed; right: 2%; bottom: 2%;">nouvelle opération</button>
      <?php endif; ?>
    </div>

    <!-- Modal Journal Add -->
    <div class="modal fade" id="journalAddModal" tabindex="-1" aria-labelledby="journalAddLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <form method="post" action="?page=journal&action=add" id="journal-add-form" onsubmit="return validateJournalForm();">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
            <div class="modal-header">
              <h5 class="modal-title" id="journalAddLabel">Nouvelle écriture (partie double)</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row mb-2 g-2">
                <div class="col-md-2">
                  <input type="date" name="date" class="form-control" id="date" required>
                </div>
                <div class="col-md-2">
                  <input type="text" name="lieu" class="form-control" id="lieu" placeholder="Lieu d'opération" required
                    maxlength="64">
                </div>

                <!-- Compte débit -->
                <!-- Recherche compte (assigner au débit/crédit) -->
                <div class="col-12">
                  <label for="compte_search" class="form-label">Rechercher un compte (Excel)</label>
                  <div class="position-relative">
                    <input type="text" id="compte_search" class="form-control" placeholder="Tapez code ou libellé">
                    <div id="compte_suggestions" class="list-group"
                      style="position:absolute;z-index:1050;width:100%;max-height:240px;overflow:auto;display:none;">
                    </div>
                  </div>
                </div>

                <!-- Compte débit (recherche) -->
                <div class="col-md-3">
                  <label class="form-label">Compte Débit</label>
                  <div class="input-group">
                    <input type="text" id="compte_debit_display" class="form-control"
                      placeholder="Aucun compte sélectionné" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="compte_debit_clear"
                      title="Effacer">✕</button>
                  </div>
                  <input type="hidden" name="compte_debit" id="compte_debit">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Intitulé débit</label>
                  <input type="text" name="intitule_debit" id="intitule_debitInput" class="form-control"
                    placeholder="Intitulé débit" maxlength="64" required>
                </div>

                <!-- Compte crédit (recherche) -->
                <div class="col-md-3">
                  <label class="form-label">Compte Crédit</label>
                  <div class="input-group">
                    <input type="text" id="compte_credit_display" class="form-control"
                      placeholder="Aucun compte sélectionné" readonly>
                    <button class="btn btn-outline-secondary" type="button" id="compte_credit_clear"
                      title="Effacer">✕</button>
                  </div>
                  <input type="hidden" name="compte_credit" id="compte_credit">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Intitulé crédit</label>
                  <input type="text" name="intitule_credit" id="intitule_creditInput" class="form-control"
                    placeholder="Intitulé crédit" maxlength="64" required>
                </div>

                <div class="col-md-6">
                  <input type="text" name="libelle" class="form-control" id="libelle" placeholder="Libellé" required
                    maxlength="64">
                </div>

                <div class="col-md-3">
                  <input type="number" step="0.01" name="debit" class="form-control" id="debit" placeholder="Débit"
                    required>
                </div>
                <div class="col-md-3">
                  <input type="number" step="0.01" name="credit" class="form-control" id="credit" placeholder="Crédit"
                    required>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
              <button type="submit" class="btn btn-success">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
  <script>
  // Use shared AccountSearch helper
  document.addEventListener('DOMContentLoaded', function() {
    AccountSearch.fetchComptes().then(function() {

      AccountSearch.createSuggestionBox({
        inputId: 'compte_search',
        suggestionsId: 'compte_suggestions',
        renderItemHtml: function(c) {
          return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-primary" data-action="debit">Débit</button>
                <button type="button" class="btn btn-secondary" data-action="credit">Crédit</button>
              </div>`;
        },
        onChoose: function(item, extra) {
          var action = extra && extra.action;
          if (!action) {
            // keyboard selection: ask the user which side to assign
            if (confirm('Affecter le compte au Débit ? OK = Débit, Annuler = Crédit')) action = 'debit';
            else action = 'credit';
          }
          assignCompteToSide(item.code, action);
        }
      });
    }).catch(console.error);
  });

  function assignCompteToSide(code, side) {
    const acc = (window.comptesList || []).find(c => c.code === code);
    if (!acc) return;
    const hidden = document.getElementById('compte_' + side);
    const display = document.getElementById('compte_' + side + '_display');
    const intituleInput = document.getElementById('intitule_' + side + 'Input');
    if (hidden) hidden.value = acc.code;
    if (display) display.value = acc.code + ' — ' + (acc.label || '');
    if (intituleInput) {
      intituleInput.value = acc.intitule || '';
      intituleInput.readOnly = true;
    }
  }

  function clearCompteSide(side) {
    const hidden = document.getElementById('compte_' + side);
    const display = document.getElementById('compte_' + side + '_display');
    const intituleInput = document.getElementById('intitule_' + side + 'Input');
    if (hidden) hidden.value = '';
    if (display) display.value = '';
    if (intituleInput) {
      intituleInput.value = '';
      intituleInput.readOnly = false;
    }
  }

  // attach clear button handlers if present
  if (document.getElementById('compte_debit_clear')) {
    document.getElementById('compte_debit_clear').addEventListener('click', function() {
      clearCompteSide('debit');
    });
  }
  if (document.getElementById('compte_credit_clear')) {
    document.getElementById('compte_credit_clear').addEventListener('click', function() {
      clearCompteSide('credit');
    });
  }

  function updateIntitule(side) {
    // Keep for backward compatibility: if a legacy select exists, handle it; otherwise no-op
    var select = document.getElementById('compte_' + side + 'Select');
    var intituleInput = document.getElementById('intitule_' + side + 'Input');
    var hiddenInput = document.getElementById('compte_' + side);
    if (!select || !intituleInput || !hiddenInput) return;
    var selected = select.options[select.selectedIndex];
    if (selected && selected.value && selected.dataset.intitule) {
      intituleInput.value = selected.dataset.intitule;
      intituleInput.readOnly = true;
      hiddenInput.value = selected.value;
    } else {
      intituleInput.value = '';
      intituleInput.readOnly = false;
      hiddenInput.value = '';
    }
  }

  function validateJournalForm() {
    const date = document.getElementById('date').value;
    const compteDeb = document.getElementById('compte_debit').value.trim();
    const compteCre = document.getElementById('compte_credit').value.trim();
    const intituleDeb = document.getElementById('intitule_debitInput').value.trim();
    const intituleCre = document.getElementById('intitule_creditInput').value.trim();
    const libelle = document.getElementById('libelle').value.trim();
    const debit = document.getElementById('debit').value;
    const credit = document.getElementById('credit').value;
    let errors = [];
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) errors.push('Date invalide');
    if (compteDeb.length < 1 || compteDeb.length > 32) errors.push('Compte débit invalide');
    if (compteCre.length < 1 || compteCre.length > 32) errors.push('Compte crédit invalide');
    if (intituleDeb.length < 1 || intituleDeb.length > 64) errors.push('Intitulé compte débit invalide');
    if (intituleCre.length < 1 || intituleCre.length > 64) errors.push('Intitulé compte crédit invalide');
    if (libelle.length < 1 || libelle.length > 64) errors.push('Libellé invalide');
    if (!debit || isNaN(debit) || parseFloat(debit) <= 0) errors.push('Débit invalide');
    if (!credit || isNaN(credit) || parseFloat(credit) <= 0) errors.push('Crédit invalide');
    if (!isNaN(debit) && !isNaN(credit) && Math.abs(parseFloat(debit) - parseFloat(credit)) > 0.001) errors.push(
      'Le montant débit doit être égal au montant crédit');
    if (errors.length) {
      alert(errors.join('\n'));
      return false;
    }
    return true;
  }

  // AJAX submit: keep Journal modal open after successful add and reset form for the next entry
  (function(){
    document.addEventListener('DOMContentLoaded', function(){
      const form = document.getElementById('journal-add-form');
      if (!form || !window.fetch || !window.FormData) return;
      const submitBtn = form.querySelector('button[type="submit"]');
      function setLoading(on, text){ if(!submitBtn) return; submitBtn.disabled = on; submitBtn.innerHTML = on ? (text||'En cours...') : 'Ajouter'; }
      form.addEventListener('submit', function(ev){
        // let native validation show messages if invalid
        if (!validateJournalForm()) return;
        // prevent full-page submit when JS available
        ev.preventDefault();
        setLoading(true, '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>  Envoi');
        const fd = new FormData(form);
        fetch('?page=journal&action=add', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        }).then(async (res) => {
          setLoading(false);
          const ctype = (res.headers.get('content-type')||'');
          const isJson = ctype.indexOf('application/json') !== -1;
          if (!res.ok) {
            const txt = await res.text().catch(()=>res.statusText);
            throw new Error(txt || 'Erreur réseau');
          }
          if (!isJson) {
            const body = await res.text().catch(()=>'<no-body>');
            const snippet = (body || '').trim().slice(0,2000);
            throw new Error('Réponse inattendue — serveur a renvoyé du HTML/texte:\n' + snippet);
          }
          return res.json();
        }).then((json) => {
          if (!json || !json.success) throw new Error(json && json.error ? json.error : 'Échec');
          // keep modal open; reset form and focus for next entry
          form.reset();
          form.querySelector('input,select,textarea')?.focus();
          const notice = document.createElement('div');
          notice.className = 'alert alert-success mt-3';
          notice.textContent = 'Écriture ajoutée — prête pour la suivante.';
          form.closest('.modal-body')?.prepend(notice);
          setTimeout(()=> notice.remove(), 2000);
        }).catch((err)=>{
          setLoading(false);
          alert('Échec : ' + (err && err.message ? err.message : 'Erreur lors de l\'envoi'));
        });
      });
    });
  })();

  // Filter compte search suggestions
  document.addEventListener('DOMContentLoaded', function() {
    AccountSearch.fetchComptes().then(function() {
      // if server provided a filter code, try to show label
      var initial = document.getElementById('filter_compte').value;
      if (initial) {
        var found = (window.comptesList || []).find(c => c.code === initial);
        if (found) document.getElementById('filter_compte_display').value = found.code + ' — ' + (found.label ||
          '');
      }

      AccountSearch.createSuggestionBox({
        inputId: 'filter_compte_display',
        suggestionsId: 'filter_compte_suggestions',
        renderItemHtml: function(c) {
          return `<div><strong>${AccountSearch.escapeHtml(c.code)}</strong> — ${AccountSearch.escapeHtml(c.label)}</div>`;
        },
        onChoose: function(item) {
          if (!item) return;
          document.getElementById('filter_compte_display').value = item.code + ' — ' + (item.label || '');
          document.getElementById('filter_compte').value = item.code;
        }
      });

      // clear hidden value if user clears display
      document.getElementById('filter_compte_display').addEventListener('input', function() {
        if (!this.value) document.getElementById('filter_compte').value = '';
      });
    }).catch(console.error);
  });
  </script>

</html>