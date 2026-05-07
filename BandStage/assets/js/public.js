/**
 * BandStage — public.js
 * Helpers globaux : BsToast, bsAjax, Tchache front.
 * Author: Pierre Beaubié
 * @license GPL-2.0-or-later
 */
/* global BsPublic */
'use strict';

// ============================================================
// BsToast — notification inline
// ============================================================
window.BsToast = (function () {
  let _timer = null;

  function show(message, type = 'success', duration = 3200) {
    const el = document.getElementById('bs-toast');
    if (!el) return;

    el.textContent = message;
    el.className   = `bss-toast bss-toast--${type} bss-toast--show`;

    clearTimeout(_timer);
    _timer = setTimeout(() => {
      el.classList.remove('bss-toast--show');
    }, duration);
  }

  return { show };
})();

// ============================================================
// bsAjax — wrapper fetch vers admin-ajax.php
// ============================================================
window.bsAjax = async function (action, data = {}) {
  const body = new FormData();
  body.append('action', action);
  body.append('nonce',  BsPublic.nonce);
  Object.entries(data).forEach(([k, v]) => body.append(k, v));

  const res  = await fetch(BsPublic.ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
  const json = await res.json();
  return json; // { success: bool, data: {...} }
};

// ============================================================
// Tchache — envoi de message
// ============================================================
(function () {
  const form     = document.getElementById('bs-tc-form');
  const messages = document.getElementById('bs-tc-messages');
  const counter  = document.getElementById('bs-tc-count');

  if (!form) return;

  // Compteur de caractères
  const ta = form.querySelector('textarea[name="content"]');
  if (ta && counter) {
    ta.addEventListener('input', () => {
      counter.textContent = ta.value.length;
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const content = ta ? ta.value.trim() : '';
    if (!content) return;

    const btn = form.querySelector('[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = BsPublic.i18n.posting; }

    const json = await bsAjax('bs_tchache_post', { content });

    if (btn) { btn.disabled = false; btn.textContent = 'Envoyer'; }

    if (json.success) {
      BsToast.show(json.data.message, 'success');
      if (ta) ta.value = '';
      if (counter) counter.textContent = '0';
      // Recharger les messages
      const load = await bsAjax('bs_tchache_load');
      if (load.success && messages) {
        messages.innerHTML = load.data.html;
      }
    } else {
      BsToast.show(json.data.message || BsPublic.i18n.error, 'error');
    }
  });
})();

// ============================================================
// Ticker — pause au hover (CSS gère l'animation)
// ============================================================
(function () {
  const ticker = document.querySelector('.bs-ticker__track');
  if (!ticker) return;
  ticker.addEventListener('mouseenter', () => ticker.style.animationPlayState = 'paused');
  ticker.addEventListener('mouseleave', () => ticker.style.animationPlayState = 'running');
})();
