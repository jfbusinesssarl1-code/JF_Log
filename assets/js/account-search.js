(function(){
  // Simple helper to fetch comptes once
  async function fetchComptes(){
    if (window.comptesList) return window.comptesList;
    try {
      const r = await fetch('?page=api&action=comptes');
      if (!r.ok) {
        const txt = await r.text().catch(()=>r.statusText || '');
        throw new Error('API error ' + r.status + ' — ' + String(txt).slice(0,200));
      }
      const comptes = await r.json();
      window.comptesList = comptes || [];

      // If server returned 0 comptes, surface a console hint with diagnostic headers
      if (!window.comptesList.length) {
        try {
          const exists = r.headers.get('X-Plan-Exists');
          const readable = r.headers.get('X-Plan-Readable');
          const count = r.headers.get('X-Plan-Count');
          console.warn('AccountSearch: comptes list is empty — X-Plan-Exists=' + exists + ' X-Plan-Readable=' + readable + ' X-Plan-Count=' + count);
        } catch (er) { /* ignore */ }
      }

      return window.comptesList;
    } catch (e) {
      console.error('AccountSearch.fetchComptes error', e);
      window.comptesList = [];
      return [];
    }
  }

  function escapeHtml(s) { return String(s||'').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
  function escapeAttr(s) { return String(s||'').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }
  function debounce(fn, wait){ let t; return function(){ const args = arguments; clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }; }

  // opts: { inputId, suggestionsId, renderItemHtml(item), onChoose(item, extra) }
  function createSuggestionBox(opts){
    const input = document.getElementById(opts.inputId);
    const suggestions = document.getElementById(opts.suggestionsId);
    if (!input || !suggestions) return null;
    let currentResults = [];
    let highlighted = -1;

    function render(results){
      suggestions.innerHTML = '';
      currentResults = results || [];
      highlighted = -1;
      currentResults.forEach((c,i)=>{
        const div = document.createElement('div');
        div.className = 'list-group-item d-flex justify-content-between align-items-center';
        div.dataset.index = i;
        // special meta rows (server empty / no-results) => render helpful UI
        if (c && c._meta) {
          if (c._meta === 'no-accounts') {
            div.classList.add('text-muted');
            div.innerHTML = '<div>Aucun compte trouvé — le serveur n\'a renvoyé aucune donnée.</div><div><button class="btn btn-sm btn-outline-secondary" data-action="retry">Réessayer</button></div>';
          } else if (c._meta === 'no-results') {
            div.classList.add('text-muted');
            div.innerHTML = '<div>Aucun résultat pour cette recherche</div>';
          } else {
            div.innerHTML = opts.renderItemHtml(c);
          }
        } else {
          div.innerHTML = opts.renderItemHtml(c);
        }
        suggestions.appendChild(div);
      });
      suggestions.style.display = currentResults.length ? '' : 'none';
    }

    async function onInput(){
      const q = input.value.trim().toLowerCase();
      suggestions.innerHTML = '';
      console.log('AccountSearch: input=', q, 'for', opts.inputId);
      const comptes = await fetchComptes();
      if (!q) {
        // show all suggestions when the input is empty (user expects la liste complète).
        // To avoid freezing the UI in pathological cases we cap at 1000 items.
        const results = (comptes || []).slice(0, 1000);
        console.log('AccountSearch: showing top', results.length, 'items for empty query');
        if (!results.length) {
          // show helpful retry / info row when server returned no accounts
          render([{ _meta: 'no-accounts' }]);
        } else {
          render(results);
        }
        return;
      }
      const results = comptes.filter(c => (c.code||'').toLowerCase().includes(q) || (c.label||'').toLowerCase().includes(q)).slice(0,1000);
      console.log('AccountSearch: found', results.length, 'results for query', q);
      if (!results.length) render([{ _meta: 'no-results' }]); else render(results);
    }

    input.addEventListener('input', debounce(onInput, 180));
    // show suggestions on focus as well
    input.addEventListener('focus', onInput);

    function highlight(idx){
      const children = suggestions.children;
      if (!children || !children.length) return;
      if (highlighted >= 0 && children[highlighted]) children[highlighted].classList.remove('active');
      highlighted = idx;
      if (highlighted >= 0 && children[highlighted]) children[highlighted].classList.add('active');
      // ensure visible
      if (children[highlighted]) children[highlighted].scrollIntoView({block:'nearest'});
    }

    input.addEventListener('keydown', function(e){
      if (suggestions.style.display === 'none') return;
      if (e.key === 'ArrowDown'){ e.preventDefault(); highlight(Math.min(highlighted+1, currentResults.length-1)); }
      else if (e.key === 'ArrowUp'){ e.preventDefault(); highlight(Math.max(highlighted-1, 0)); }
      else if (e.key === 'Enter'){ e.preventDefault(); if (highlighted >= 0) { choose(currentResults[highlighted]); } }
      else if (e.key === 'Escape'){ suggestions.style.display = 'none'; }
    });

    function choose(item, extra){
      try { if (typeof opts.onChoose === 'function') opts.onChoose(item, extra || {}); } catch (e){ console.error('AccountSearch.onChoose error', e); }
      suggestions.style.display = 'none';
      input.value = '';
    }

    suggestions.addEventListener('click', function(e){
      const btn = e.target.closest('[data-action]');
      if (btn){
        const li = btn.closest('.list-group-item');
        if (!li) return;
        const idx = parseInt(li.dataset.index, 10);
        const item = currentResults[idx];
        const action = btn.dataset.action;
        choose(item, { action });
        return;
      }
      const li = e.target.closest('.list-group-item');
      if (li){
        const idx = parseInt(li.dataset.index, 10);
        const item = currentResults[idx];
        choose(item);
      }
    });

    document.addEventListener('click', function(e){
      if (e.target !== input && !suggestions.contains(e.target)) suggestions.style.display = 'none';
    });

    return { render, choose };
  }

  // expose
  window.AccountSearch = {
    fetchComptes,
    createSuggestionBox,
    escapeHtml,
    escapeAttr,
    debounce
  };
})();
