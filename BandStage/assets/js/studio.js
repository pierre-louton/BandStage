/**
 * BandStage — studio.js
 * CRUD news / partenaires / lineup, wp.media, drag-and-drop réordonnancement.
 * Author: Pierre Beaubié
 * @license GPL-2.0-or-later
 */
/* global BsPublic, bsAjax, BsToast, wp */
'use strict';

// ============================================================
// 1. DELETE NEWS
// ============================================================
document.querySelectorAll('.js-news-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;

    btn.disabled = true;
    const json   = await bsAjax('bs_news_delete', { post_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('[data-id], li');
      if (row) {
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 300);
      } else if (btn.dataset.redirect) {
        window.location.href = btn.dataset.redirect;
      }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});

// ============================================================
// 2. SAVE NEWS (form bss-news-form)
// ============================================================
(function () {
  const form = document.getElementById('bss-news-form');
  if (!form) return;

  // Bouton submit hors form
  document.querySelectorAll('.js-news-save').forEach(btn => {
    btn.addEventListener('click', () => {
      const statusInput = document.getElementById('bss-news-status');
      if (statusInput && btn.dataset.status) {
        statusInput.value = btn.dataset.status;
      }
    });
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    const btn  = document.querySelector('[form="bss-news-form"]');
    if (btn) btn.disabled = true;

    const json = await bsAjax('bs_news_save', data);
    if (btn) btn.disabled = false;

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      setTimeout(() => { window.location.href = json.data.redirect; }, 900);
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// 3. DELETE PARTENAIRE
// ============================================================
document.querySelectorAll('.js-partenaire-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;
    btn.disabled = true;
    const json = await bsAjax('bs_partenaire_delete', { partenaire_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('.bss-partenaire-item');
      if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});

// ============================================================
// 4. SAVE PARTENAIRE
// ============================================================
(function () {
  const form = document.getElementById('bss-partenaire-form');
  if (!form) return;

  // Prévisualisation du logo sélectionné
  const fileInput = document.getElementById('bs-logo-file');
  const preview   = document.getElementById('bs-logo-preview');
  if (fileInput && preview) {
    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => { preview.innerHTML = `<img src="${e.target.result}" alt="">`; };
      reader.readAsDataURL(file);
    });
  }

  // Retrait du logo
  const removeBtn = form.querySelector('.js-logo-remove');
  if (removeBtn) {
    removeBtn.addEventListener('click', () => {
      if (preview) preview.innerHTML = '<span class="bss-logo-preview__placeholder">🖼️</span>';
      const actionInput = document.getElementById('bs-logo-action');
      if (actionInput) actionInput.value = 'remove';
      removeBtn.style.display = 'none';
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.set('action', 'bs_partenaire_save');
    formData.set('nonce', BsPublic.nonce);
    const btn = document.querySelector('[form="bss-partenaire-form"]');
    if (btn) btn.disabled = true;

    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
      const json = await res.json();
      if (btn) btn.disabled = false;
      if (json.success) {
        BsToast.show(json.data.message, 'success');
        setTimeout(() => { window.location.href = json.data.redirect; }, 900);
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      if (btn) btn.disabled = false;
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// 5. LINEUP — save
// ============================================================
(function () {
  const form = document.getElementById('bss-lineup-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    const btn  = document.querySelector('[form="bss-lineup-form"]');
    if (btn) { btn.disabled = true; btn.textContent = BsPublic.i18n.posting; }

    const json = await bsAjax('bs_lineup_save', data);

    if (btn) { btn.disabled = false; btn.textContent = 'Enregistrer'; }

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      setTimeout(() => { window.location.href = json.data.redirect; }, 900);
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// 6. LINEUP — delete
// ============================================================
document.querySelectorAll('.js-lineup-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;
    btn.disabled = true;
    const json   = await bsAjax('bs_lineup_delete', { post_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('.bss-lineup-item');
      if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});

// ============================================================
// 7. LINEUP — drag-and-drop réordonnancement (natif HTML5)
// ============================================================
(function () {
  const list = document.getElementById('bs-lineup-sortable');
  if (!list) return;

  let dragging = null;

  list.addEventListener('dragstart', e => {
    dragging = e.target.closest('.bss-lineup-item');
    if (dragging) {
      dragging.classList.add('is-dragging');
      e.dataTransfer.effectAllowed = 'move';
    }
  });

  list.addEventListener('dragover', e => {
    e.preventDefault();
    const target = e.target.closest('.bss-lineup-item');
    if (target && target !== dragging) {
      const rect   = target.getBoundingClientRect();
      const after  = e.clientY > rect.top + rect.height / 2;
      list.insertBefore(dragging, after ? target.nextSibling : target);
    }
  });

  list.addEventListener('dragend', async () => {
    if (dragging) dragging.classList.remove('is-dragging');
    dragging = null;

    // Envoyer le nouvel ordre
    const ids = [...list.querySelectorAll('.bss-lineup-item')].map(el => el.dataset.id);
    await bsAjax('bs_lineup_reorder', { 'order[]': ids });
  });

  // Rendre les items draggables
  list.querySelectorAll('.bss-lineup-item').forEach(item => {
    item.setAttribute('draggable', 'true');
  });
})();

// ============================================================
// 8. WP.MEDIA — sélecteur de médias
// ============================================================
(function () {
  if (typeof wp === 'undefined' || !wp.media) return;

  document.querySelectorAll('.js-media-select').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId  = btn.dataset.target;
      const previewId = btn.dataset.preview;

      const frame = wp.media({
        title   : 'Choisir une image',
        button  : { text: 'Utiliser cette image' },
        multiple: false,
        library : { type: 'image' },
      });

      frame.on('select', () => {
        const attachment = frame.state().get('selection').first().toJSON();
        const input      = document.getElementById(targetId);
        const preview    = document.getElementById(previewId);

        if (input) input.value = attachment.id;

        if (preview) {
          const url = attachment.sizes?.medium?.url || attachment.url;
          preview.innerHTML = `<img src="${url}" alt="">`;
        }
      });

      frame.open();
    });
  });

  // Retirer la vignette
  document.querySelectorAll('.js-media-remove').forEach(btn => {
    btn.addEventListener('click', () => {
      const input   = document.getElementById(btn.dataset.target);
      const preview = document.getElementById(btn.dataset.preview);
      if (input)   input.value = '0';
      if (preview) preview.innerHTML = '<span class="bss-media-preview__placeholder">📷</span>';
      btn.style.display = 'none';
    });
  });
})();

// ============================================================
// 9. CONCERT — delete
// ============================================================
document.querySelectorAll('.js-concert-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;
    btn.disabled = true;
    const json = await bsAjax('bs_concert_delete', { concert_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('.bss-concert-item');
      if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});

// ============================================================
// 10. CONCERT — save (FormData natif pour le multi-select)
// ============================================================
(function () {
  const form = document.getElementById('bss-concert-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.set('action', 'bs_concert_save');
    formData.set('nonce', BsPublic.nonce);
    const btn = document.querySelector('[form="bss-concert-form"]');
    if (btn) btn.disabled = true;

    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
      const json = await res.json();
      if (btn) btn.disabled = false;
      if (json.success) {
        BsToast.show(json.data.message, 'success');
        setTimeout(() => { window.location.href = json.data.redirect; }, 900);
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      if (btn) btn.disabled = false;
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// 11. MORCEAU — delete
// ============================================================
document.querySelectorAll('.js-morceau-delete').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(BsPublic.i18n.confirm)) return;
    btn.disabled = true;
    const json = await bsAjax('bs_morceau_delete', { morceau_id: btn.dataset.id });

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      const row = btn.closest('.bss-morceau-item');
      if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 300); }
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      btn.disabled = false;
    }
  });
});

// ============================================================
// 12. MORCEAU — save (FormData pour multi-select style_ids[])
// ============================================================
(function () {
  const form = document.getElementById('bss-morceau-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.set('action', 'bs_morceau_save');
    formData.set('nonce', BsPublic.nonce);
    const btn = document.querySelector('[form="bss-morceau-form"]');
    if (btn) btn.disabled = true;

    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' });
      const json = await res.json();
      if (btn) btn.disabled = false;
      if (json.success) {
        BsToast.show(json.data.message, 'success');
        setTimeout(() => { window.location.href = json.data.redirect; }, 900);
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      if (btn) btn.disabled = false;
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// 13. STYLE — delete (délégation sur document, vanilla JS)
// ============================================================
document.addEventListener('click', async function (e) {
  const btn = e.target.closest('.js-style-delete');
  if (!btn) return;
  if (!confirm(BsPublic.i18n.confirm)) return;
  const form = new FormData();
  form.append('action', 'bs_style_delete');
  form.append('style_id', btn.dataset.id);
  form.append('nonce', BsPublic.nonce);
  try {
    const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: form, credentials: 'same-origin' });
    const json = await res.json();
    if (json.success) {
      BsToast.show(json.data.message, 'success');
      btn.closest('tr').remove();
    } else {
      BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
    }
  } catch {
    BsToast.show(BsPublic.i18n.error, 'error');
  }
});

// ============================================================
// 14. STYLE — toggle form visibility
// ============================================================
(function () {
  const toggleBtn = document.querySelector('.js-style-form-toggle');
  const formWrap  = document.getElementById('bss-style-form-wrap');
  if (!toggleBtn || !formWrap) return;

  toggleBtn.addEventListener('click', () => {
    const hidden = formWrap.hasAttribute('hidden');
    if (hidden) {
      formWrap.removeAttribute('hidden');
    } else {
      formWrap.setAttribute('hidden', '');
    }
  });
})();

// ============================================================
// 15. STYLE — save (inline form)
// ============================================================
(function () {
  const styleForm = document.getElementById('bss-style-form');
  const formWrap  = document.getElementById('bss-style-form-wrap');
  if (!styleForm) return;

  styleForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(styleForm);
    data.set('action', 'bs_style_save');
    data.set('nonce', BsPublic.nonce);
    try {
      const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' });
      const json = await res.json();
      if (json.success) {
        const d   = json.data;
        const img = d.image_url
          ? `<img src="${d.image_url}" class="bss-styles-table__thumb" loading="lazy">`
          : '<span class="bss-styles-table__noimg">—</span>';
        const tbody = document.getElementById('bss-styles-list');
        if (tbody) {
          const tr = document.createElement('tr');
          tr.dataset.id = d.style_id;
          tr.innerHTML = `
            <td>${escHtml(d.nom_style)}</td>
            <td>${img}</td>
            <td><button type="button" class="bss-btn bss-btn--sm bss-btn--danger js-style-delete"
                data-id="${d.style_id}" data-nonce="${BsPublic.nonce}">Supprimer</button></td>`;
          tbody.appendChild(tr);
        }
        styleForm.reset();
        if (formWrap) formWrap.setAttribute('hidden', '');
        BsToast.show(json.data.message, 'success');
      } else {
        BsToast.show(json.data?.message || BsPublic.i18n.error, 'error');
      }
    } catch {
      BsToast.show(BsPublic.i18n.error, 'error');
    }
  });

  function escHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
})();
