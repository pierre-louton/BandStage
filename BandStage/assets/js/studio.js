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
    const json   = await bsAjax('bs_partenaire_delete', { post_id: btn.dataset.id });

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
// 4. SAVE PARTENAIRE (form bss-partenaire-form)
// ============================================================
(function () {
  const form = document.getElementById('bss-partenaire-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    const btn  = document.querySelector('[form="bss-partenaire-form"]');
    if (btn) btn.disabled = true;

    const json = await bsAjax('bs_partenaire_save', data);
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
