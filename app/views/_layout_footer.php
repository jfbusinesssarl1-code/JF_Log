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