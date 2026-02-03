<?php
if (session_status() === PHP_SESSION_NONE)
  session_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <?php $title = 'Livre de caisse';
  require __DIR__ . '/_layout_head.php'; ?>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <a class="btn btn-outline-secondary me-2" href="?page=caisse&action=export&format=pdf">Exporter PDF</a>
        <?php if (!class_exists('\\Mpdf\\Mpdf')): ?>
        <button class="btn btn-outline-info" type="button" onclick="exportCaisseToPDFClient()">Exporter PDF
          (imprimer)</button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-4">
      <div class="card-header bg-light">
        <h5 class="mb-0">Filtres</h5>
      </div>
      <div class="card-body">
        <form method="get" class="row g-2">
          <input type="hidden" name="page" value="caisse">
          <div class="col-md-2">
            <input type="date" name="date_debut" class="form-control"
              value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <input type="date" name="date_fin" class="form-control"
              value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <input type="text" name="operateur" class="form-control" placeholder="Nom opérateur"
              value="<?= htmlspecialchars($filters['operateur'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <input type="text" name="numero_bon_manuscrit" class="form-control" placeholder="Numéro du bon"
              value="<?= htmlspecialchars($filters['numero_bon_manuscrit'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <select name="type" class="form-select">
              <option value="">Tous</option>
              <option value="entree" <?= ($filters['type'] ?? '') === 'entree' ? 'selected' : '' ?>>Bon d'entrée
              </option>
              <option value="sortie" <?= ($filters['type'] ?? '') === 'sortie' ? 'selected' : '' ?>>Bon de sortie
              </option>
            </select>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <a href="?page=caisse" class="btn btn-secondary w-100">Tous</a>
          </div>
        </form>
      </div>
    </div>

    <div class="table-responsive" style="padding-bottom:88px;">

      <h2 class="mb-0 fw-bold fs-2">Livre de caisse</h2>
      <table class="table table-bordered">
        <thead>
          <tr class="table-secondary table-gradient">
            <th>Date</th>
            <th>Type</th>
            <th>N° Bon Manuel</th>
            <th>Opérateur</th>
            <th>Libellé</th>
            <th class="text-end">Recette</th>
            <th class="text-end">Dépense</th>
            <th class="text-end">Solde</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($items)):
            foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['type'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['numero_bon_manuscrit'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['operateur'] ?? '') ?></td>
            <td><?= htmlspecialchars($it['libelle'] ?? '') ?></td>
            <td class="text-end"><?= number_format($it['recette'] ?? 0, 2) ?></td>
            <td class="text-end"><?= number_format($it['depense'] ?? 0, 2) ?></td>
            <td class="text-end"><?= number_format($it['solde'] ?? 0, 2) ?></td>
            <td>
              <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['caissier', 'admin'])): ?>
              <a class="btn btn-sm btn-primary" href="?page=caisse&action=edit&id=<?= $it['_id'] ?? '' ?>">Modifier</a>
              <a class="btn btn-sm btn-danger" href="?page=caisse&action=delete&id=<?= $it['_id'] ?? '' ?>"
                onclick="return confirm('Supprimer ?')">Supprimer</a>
              <?php else: ?>
              —
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="9" class="text-center">Aucune opération</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Add Entry Section -->

    <?php if (!class_exists('\\Mpdf\\Mpdf')): ?>
    <div class="alert alert-warning">Le serveur ne dispose pas du générateur PDF (<code>mpdf/mpdf</code>). Cliquez sur
      <strong>Exporter PDF (imprimer)</strong> pour ouvrir une version imprimable et enregistrer en PDF via votre
      navigateur, ou installez <code>composer require mpdf/mpdf</code> pour des PDF côté serveur.
    </div>
    <?php endif; ?>

    <script>
    function exportCaisseToPDFClient() {
      // Collect table HTML
      const table = document.querySelector('.table-responsive .table');
      if (!table) return alert('Tableau introuvable');
      const html = `<!doctype html><html><head><meta charset="utf-8"><title>Livre de caisse</title>` +
        '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">' +
        '</head><body class="p-4">' +
        '<h2>Livre de caisse</h2>' + table.outerHTML +
        '<script>window.onload = function(){ window.print(); }</' + 'script>' +
        '</body></html>';
      const w = window.open('', '_blank');
      w.document.write(html);
      w.document.close();
    }
    </script>

    <!-- bouton pour ouvrir le modal (seulement caissier + admin) -->
    <?php if (isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['caissier', 'admin'])): ?>
    <button type="button" class="btn btn-primary btn-fab-caisse d-none d-md-inline-flex" data-bs-toggle="modal"
      data-bs-target="#caisseModal" aria-label="Ajouter une opération">
      nouvelle opération
    </button>
    <?php endif; ?>



    <!-- Modal: formulaire de la caisse -->
    <div class="modal fade" id="caisseModal" tabindex="-1" aria-labelledby="caisseModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="caisseModalLabel">Ajouter une opération</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <form method="post" action="?page=caisse&action=add" id="caisse-modal-form">
              <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::generateToken() ?>">
              <div class="row g-3">
                <div class="col-md-3">
                  <label for="caisse_date" class="form-label small">Date</label>
                  <input id="caisse_date" type="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-3">
                  <label for="caisse_type" class="form-label small">Type</label>
                  <select id="caisse_type" name="type" class="form-select" required>
                    <option value="">Sélectionner type</option>
                    <option value="entree">Bon d'entrée</option>
                    <option value="sortie">Bon de sortie</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="caisse_numero_bon_manuscrit" class="form-label small">N° Bon</label>
                  <input id="caisse_numero_bon_manuscrit" type="text" name="numero_bon_manuscrit" class="form-control"
                    placeholder="N° Bon Manuscrit" required>
                </div>
                <div class="col-md-3">
                  <label for="caisse_operateur" class="form-label small">Opérateur</label>
                  <input id="caisse_operateur" type="text" name="operateur" class="form-control" placeholder="Opérateur"
                    required>
                </div>
                <div class="col-md-9 mt-2">
                  <label for="caisse_libelle" class="form-label small">Libellé</label>
                  <input id="caisse_libelle" type="text" name="libelle" class="form-control" placeholder="Libellé"
                    required>
                </div>
                <div class="col-md-3 mt-2">
                  <label for="caisse_montant" class="form-label small">Montant</label>
                  <input id="caisse_montant" type="number" step="0.01" name="montant" class="form-control"
                    placeholder="Montant" required>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" form="caisse-modal-form" class="btn btn-success">Ajouter</button>
          </div>
        </div>
      </div>
    </div>

    <script>
    (function() {
      const modalEl = document.getElementById('caisseModal');
      if (!modalEl) return;
      const inlineForm = document.getElementById('caisse-inline-form');
      const modalForm = document.getElementById('caisse-modal-form');

      // When modal opens: copy any values from inline form and focus date (or set today)
      modalEl.addEventListener('shown.bs.modal', function() {
        try {
          const get = (name) => (inlineForm.querySelector('[name="' + name + '"]') || {}).value || '';
          ['date', 'type', 'numero_bon_manuscrit', 'operateur', 'libelle', 'montant'].forEach((n) => {
            const el = modalForm.querySelector('[name="' + n + '"]');
            if (!el) return;
            const v = get(n);
            if (v) el.value = v;
            else if (n === 'date' && !el.value) el.value = new Date().toISOString().slice(0, 10);
          });
          const first = modalForm.querySelector('input,select,textarea');
          if (first) first.focus();
        } catch (e) {
          /* silent */
        }
      });

      // Clear modal on hide to avoid stale values
      modalEl.addEventListener('hidden.bs.modal', function() {
        try {
          modalForm.reset();
        } catch (e) {
          /* silent */
        }
      });

      // --- AJAX submit for modal (graceful fallback: form still works without JS) ---
      const submitBtn = modalEl.querySelector('button[type="submit"][form="caisse-modal-form"]');

      function setLoading(on, text) {
        if (!submitBtn) return;
        submitBtn.disabled = on;
        submitBtn.innerHTML = on ? (text || 'En cours...') : 'Ajouter';
      }
      // Validation helpers (Bootstrap compatible)
      function showFieldError(el, msg) {
        if (!el) return;
        el.classList.add('is-invalid');
        let fb = el.parentNode.querySelector('.invalid-feedback');
        if (!fb) {
          fb = document.createElement('div');
          fb.className = 'invalid-feedback';
          el.parentNode.appendChild(fb);
        }
        fb.textContent = msg || 'Champ invalide';
      }

      function clearFieldError(el) {
        if (!el) return;
        el.classList.remove('is-invalid');
        const fb = el.parentNode.querySelector('.invalid-feedback');
        if (fb) fb.textContent = '';
      }

      function validateModalForm() {
        let ok = true;
        const f = modalForm;
        ['date', 'type', 'numero_bon_manuscrit', 'operateur', 'libelle', 'montant'].forEach((name) => {
          const el = f.querySelector('[name="' + name + '"]');
          clearFieldError(el);
          if (!el) return;
          const v = (el.value || '').toString().trim();
          if (!v) {
            showFieldError(el, 'Ce champ est requis');
            ok = false;
            return;
          }
          if (name === 'montant') {
            const n = Number(v);
            if (!isFinite(n) || n <= 0) {
              showFieldError(el, 'Montant doit être > 0');
              ok = false;
            }
          }
        });
        return ok;
      }
      modalForm.addEventListener('submit', function(ev) {
        ev.preventDefault();
        // keep native fallback if JS deliberately disabled on the form element
        if (!(window.fetch && FormData)) return modalForm.submit();
        // client validation: if invalid, focus first invalid field and abort
        if (!validateModalForm()) {
          const firstInvalid = modalForm.querySelector('.is-invalid');
          if (firstInvalid) firstInvalid.focus();
          return;
        }
        setLoading(true,
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>  Envoi');
        const fd = new FormData(modalForm);
        fetch('?page=caisse&action=add', {
          method: 'POST',
          body: fd,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        }).then(async (res) => {
          setLoading(false);
          if (!res.ok) {
            const txt = await res.text().catch(() => res.statusText);
            throw new Error(txt || 'Erreur réseau');
          }
          return res.json();
        }).then((json) => {
          if (!json || !json.success) throw new Error(json && json.error ? json.error : 'Échec');

          // close modal
          try {
            const bs = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bs.hide();
          } catch (e) {
            /* silent */
          }

          // optimistic UI: append a temporary row (will be removed/replaced by server response)
          try {
            const tBody = document.querySelector('.table-responsive .table tbody');
            if (tBody && json.item) {
              const it = json.item;
              const format = (v) => (Number(v || 0)).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              });
              const recette = (it.type === 'entree') ? format(it.montant) : format(0);
              const depense = (it.type === 'sortie') ? format(it.montant) : format(0);
              const solde = (typeof it.solde !== 'undefined') ? format(it.solde) : '—';

              // avoid duplicate optimistic row if server already returned the row (fast responses)
              if (it._id && tBody.querySelector('tr[data-inserted-id="' + it._id + '"]')) {
                /* already present, skip optimistic append */
              } else {
                const row = document.createElement('tr');
                row.dataset.temp = '1';
                if (it._id) row.dataset.insertedId = it._id;
                row.innerHTML = '<td>' + (it.date || '') + '</td>' +
                  '<td>' + (it.type || '') + '</td>' +
                  '<td>' + (it.numero_bon_manuscrit || '') + '</td>' +
                  '<td>' + (it.operateur || '') + '</td>' +
                  '<td>' + (it.libelle || '') + '</td>' +
                  '<td class="text-end">' + recette + '</td>' +
                  '<td class="text-end">' + depense + '</td>' +
                  '<td class="text-end">' + solde + '</td>' +
                  '<td>\u2014</td>';
                // append temporarily (will be replaced by server partial)
                tBody.appendChild(row);
              }
            }
          } catch (e) {
            console.error('optimistic append error', e);
          }

          // show brief success notice then replace tbody via partial fetch (no full reload)
          try {
            const notice = document.createElement('div');
            notice.className = 'alert alert-success mt-3';
            notice.textContent = 'Opération ajoutée — mise à jour en cours...';
            modalForm.closest('.card')?.querySelector('.card-body')?.prepend(notice);

            // fetch updated tbody and replace it in-place; fallback to full reload on error
            fetch('?page=caisse&action=list_partial', {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
              .then(r => {
                if (!r.ok) return Promise.reject('Impossible de récupérer la liste (status ' + r.status +
                  ')');
                const rowCount = Number(r.headers.get('X-Row-Count') || -1);
                return r.text().then(html => ({
                  html,
                  rowCount
                }));
              })
              .then(({
                html,
                rowCount
              }) => {
                const tBody = document.querySelector('.table-responsive .table tbody');
                if (!tBody) return location.reload();
                const tmp = document.createElement('tbody');
                tmp.innerHTML = html.trim();
                const rows = tmp.querySelectorAll('tr');
                if (!rows.length) return location.reload();

                // Replace safely and remove any optimistic temp rows
                tBody.innerHTML = tmp.innerHTML;
                tBody.querySelectorAll('tr[data-temp="1"]').forEach(n => n.remove());

                // Remove transient notice and trigger a layout refresh
                const notice = modalForm.closest('.card')?.querySelector('.alert-success');
                if (notice) setTimeout(() => notice.remove(), 1500);
                window.dispatchEvent(new Event('resize'));

                if (rowCount >= 0 && rowCount !== tBody.querySelectorAll('tr').length) {
                  console.warn('Caisse: expected rows', rowCount, 'but DOM has', tBody.querySelectorAll(
                    'tr').length);
                }
              }).catch((err) => {
                console.error('Failed to refresh caisse list:', err);
                location.reload();
              });
          } catch (e) {
            location.reload();
          }

        }).catch((err) => {
          setLoading(false);
          const msg = err && err.message ? err.message : 'Erreur lors de l\'envoi';
          alert('Échec : ' + msg);
        });
      });

    })();
    </script>

  </div>
  <?php require __DIR__ . '/_layout_footer.php'; ?>
</body>

</html>