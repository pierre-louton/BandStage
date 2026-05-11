/**
 * BandStage — admin.js
 * Back-office WP : color picker, modération Tchache, types partenaires.
 * Author: Pierre Beaubié
 * @license GPL-2.0-or-later
 */
/* global BsAdmin, jQuery */
(function ($) {
  'use strict';

  // ============================================================
  // 1. COLOR PICKERS
  // ============================================================
  $(document).ready(function () {
    $('.bs-color-picker').wpColorPicker();
  });

  // ============================================================
  // 2. MODÉRATION TCHACHE
  // ============================================================
  $(document).on('click', '.js-moderate', async function () {
    const btn    = $(this);
    const msgId  = btn.data('id');
    const action = btn.data('action');
    const nonce  = btn.data('nonce');

    if (action === 'delete' && !confirm(BsAdmin.i18n.confirm_delete)) return;

    btn.prop('disabled', true);

    const body = new FormData();
    body.append('action',          'bs_tchache_moderate');
    body.append('nonce',           nonce);
    body.append('msg_id',          msgId);
    body.append('moderate_action', action);

    const res  = await fetch(BsAdmin.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
    const json = await res.json();

    if (json.success) {
      $(`#bs-msg-${msgId}`).fadeOut(300, function () { $(this).remove(); });
    } else {
      alert(json.data?.message || 'Erreur');
      btn.prop('disabled', false);
    }
  });

  // ============================================================
  // 3. AJOUT TYPE PARTENAIRE
  // ============================================================
  $(document).on('click', '.js-add-type', async function () {
    const btn   = $(this);
    const name  = $('#bs-new-type-name').val().trim();
    const icon  = $('#bs-new-type-icon').val().trim();
    const nonce = btn.data('nonce');

    if (!name) { alert('Nom obligatoire'); return; }

    btn.prop('disabled', true);

    const body = new FormData();
    body.append('action',      'bs_type_partenaire_add');
    body.append('nonce',       nonce);
    body.append('type_name',   name);
    body.append('type_icon',   icon);

    const res  = await fetch(BsAdmin.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
    const json = await res.json();

    if (json.success) {
      $('#bs-type-list').append(
        `<li>${json.data.icon} ${json.data.name} <code>${json.data.slug}</code></li>`
      );
      $('#bs-new-type-name').val('');
      $('#bs-new-type-icon').val('');
    } else {
      alert(json.data?.message || 'Erreur');
    }

    btn.prop('disabled', false);
  });

  // ============================================================
  // 4. CRÉER PAGES MANQUANTES
  // ============================================================
  $(document).on('click', '.js-create-pages', async function () {
    const btn   = $(this);
    const nonce = btn.data('nonce');
    btn.prop('disabled', true).text('Création en cours…');

    const body = new FormData();
    body.append('action', 'bs_create_pages');
    body.append('nonce',  nonce);

    try {
      const res  = await fetch(BsAdmin.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
      const json = await res.json();
      if (json.success) {
        alert(json.data.message);
        location.reload();
      } else {
        alert(json.data?.message || 'Erreur');
        btn.prop('disabled', false).text('Créer les pages manquantes');
      }
    } catch {
      alert('Erreur réseau.');
      btn.prop('disabled', false).text('Créer les pages manquantes');
    }
  });

}(jQuery));
