<!-- Footer Section -->
<footer
  style="background: linear-gradient(135deg, #2d3436 0%, #1e1e1e 100%); color: white; margin-top: 4rem; padding: 3rem 0 2rem;">
  <div class="container">
    <div class="row g-4 mb-4">
      <!-- Adresse -->
      <div class="col-md-4 col-sm-6 col-12">
        <div class="d-flex align-items-start gap-3">
          <div style="font-size: 1.8rem; color: #fff;">📍</div>
          <div>
            <h6 class="fw-bold mb-2">Adresse</h6>
            <p class="small mb-1"><strong>Maison KIKE Oil</strong> vers la Victoire</p>
            <p class="small mb-1">Quartier Congo ya Sika</p>
            <p class="small mb-1">Ville de Butembo</p>
            <p class="small mb-1">Province du Nord-Kivu</p>
            <p class="small">Rep. Dem. du Congo</p>
          </div>
        </div>
      </div>

      <!-- Identifiants -->
      <div class="col-md-4 col-sm-6 col-12">
        <div class="d-flex align-items-start gap-3">
          <div style="font-size: 1.8rem; color: #fff;">🏛️</div>
          <div>
            <h6 class="fw-bold mb-2">Identifiants</h6>
            <p class="small mb-1"><strong>R.C.C.M :</strong> CD/KNG/RCCM/24-B-04138</p>
            <p class="small mb-1"><strong>ID-NAT :</strong> 01-F4200-N 37015G</p>
            <p class="small mb-1"><strong>N° IMPOT :</strong> A2504347D</p>
            <p class="small mb-1"><strong>N° INSS :</strong> 1022461300</p>
            <p class="small"><strong>N° INPP :</strong> A2504347D</p>
          </div>
        </div>
      </div>

      <!-- Contacts -->
      <div class="col-md-4 col-sm-6 col-12">
        <div class="d-flex align-items-start gap-3">
          <div style="font-size: 1.8rem; color: #fff;">✉️</div>
          <div>
            <h6 class="fw-bold mb-2">Nous Contacter</h6>
            <p class="small mb-2">
              <strong>WhatsApp :</strong><br>
              <a href="https://wa.me/86134802997044" target="_blank" class="text-white text-decoration-none"
                style="opacity: 0.9;">
                📱 (+86) 134 8029 9704
              </a><br>
              <a href="https://wa.me/243973096778" target="_blank" class="text-white text-decoration-none"
                style="opacity: 0.9;">
                📱 (+243) 973 096 778
              </a>
            </p>
            <p class="small">
              <strong>Gmail :</strong><br>
              <a href="mailto:jfbusinesssarl1@gmail.com" class="text-white text-decoration-none" style="opacity: 0.9;">
                📧 jfbusinesssarl1@gmail.com
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <hr style="border-color: rgba(255, 255, 255, 0.3);">

    <!-- Bottom Info -->
    <div class="row align-items-center">
      <div class="col-md-6 col-12">
        <p class="small mb-0" style="opacity: 0.85;">
          &copy; 2024 <strong>JF BUSINESS SARL</strong> — Système comptable intégré. Tous droits réservés.
        </p>
      </div>
      <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
        <p class="small mb-0" style="opacity: 0.85;">
          Développé avec ❤️ pour une comptabilité précise et fiable
        </p>
      </div>
    </div>
  </div>
</footer>

<!-- Layout footer: scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity=""
  crossorigin="anonymous"></script>
<div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
  <div id="siteToasts"></div>
</div>
<script>
  // Small helper: add subtle animations and table improvements
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('table').forEach(function (t) {
      t.classList.add('table', 'align-middle');
      t.closest('.table-responsive')?.classList.add('shadow-sm', 'rounded-3');
    });
  });

  // showTempAlert(message, type='info', timeout=3000)
  function showTempAlert(message, type = 'info', timeout = 3000) {
    const container = document.getElementById('siteToasts');
    if (!container) return;
    const id = 'toast-' + Date.now();
    const bg = (type === 'success') ? 'bg-success text-white' : (type === 'warning' ? 'bg-warning text-dark' : 'bg-secondary text-white');
    const toastEl = document.createElement('div');
    toastEl.className = 'toast ' + bg;
    toastEl.id = id;
    toastEl.role = 'alert';
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.style.minWidth = '200px';
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    `;
    container.appendChild(toastEl);
    const bToast = new bootstrap.Toast(toastEl, { delay: timeout });
    bToast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }
</script>
<script src="/asset.php?f=js/account-search.js"></script>