/* Confident Trader — front-end interactions.
   Vanilla, no dependencies, delegated so stored markup stays clean. */
(function () {
  'use strict';

  /* ------------------------------------------------------------ accordion -- */
  function togglePanel(trigger) {
    var id = trigger.getAttribute('data-ct-panel');
    if (!id) return;
    var panel = document.getElementById(id);
    if (!panel) return;
    var icon = document.getElementById(id.replace('-content', '-icon'));
    var open = panel.classList.toggle('hidden') === false;
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (icon) icon.classList.toggle('rotate-180', open);
  }

  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-ct-panel]');
    if (trigger) {
      event.preventDefault();
      togglePanel(trigger);
    }
  });

  /* ---------------------------------------------------------------- modal -- */
  function modal(open) {
    var el = document.querySelector('[data-ct-modal]');
    if (!el) return;
    el.classList.toggle('hidden', !open);
    el.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) {
      var close = el.querySelector('[data-ct-modal-close]');
      if (close) close.focus();
    } else if (window.__ctModalTrigger) {
      window.__ctModalTrigger.focus();
      window.__ctModalTrigger = null;
    }
  }

  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-ct-modal-open]');
    if (opener) {
      event.preventDefault();
      window.__ctModalTrigger = opener;
      modal(true);
      return;
    }
    if (event.target.closest('[data-ct-modal-close]') || event.target.matches('[data-ct-modal]')) {
      event.preventDefault();
      modal(false);
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    var m = document.querySelector('[data-ct-modal]:not(.hidden)');
    if (m) modal(false);
    var menu = document.getElementById('ct-mobile-menu');
    if (menu && !menu.classList.contains('hidden')) closeMenu();
  });

  /* ----------------------------------------------------------- mobile nav -- */
  var menuEl = null;
  function openMenu() {
    if (!menuEl) return;
    menuEl.classList.remove('hidden');
    document.body.classList.add('ct-menu-open');
    var btn = document.querySelector('[data-ct-menu-open]');
    if (btn) btn.setAttribute('aria-expanded', 'true');
    var first = menuEl.querySelector('[data-ct-menu-close]');
    if (first) first.focus();
  }
  function closeMenu() {
    if (!menuEl) return;
    menuEl.classList.add('hidden');
    document.body.classList.remove('ct-menu-open');
    var btn = document.querySelector('[data-ct-menu-open]');
    if (btn) {
      btn.setAttribute('aria-expanded', 'false');
      btn.focus();
    }
  }
  document.addEventListener('click', function (event) {
    if (event.target.closest('[data-ct-menu-open]')) {
      event.preventDefault();
      openMenu();
      return;
    }
    if (event.target.closest('[data-ct-menu-close]')) {
      event.preventDefault();
      closeMenu();
    }
  });

  /* --------------------------------------------- live NY session countdown -- */
  function nyParts(date) {
    var fmt = new Intl.DateTimeFormat('en-US', {
      timeZone: 'America/New_York', year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    });
    var out = {};
    fmt.formatToParts(date).forEach(function (part) { out[part.type] = part.value; });
    return {
      y: +out.year, mo: +out.month, d: +out.day,
      h: (+out.hour) % 24, mi: +out.minute, s: +out.second
    };
  }

  function nextCashOpen(now) {
    var p = nyParts(now);
    var wall = Date.UTC(p.y, p.mo - 1, p.d, p.h, p.mi, p.s);
    var target = Date.UTC(p.y, p.mo - 1, p.d, 9, 30, 0);
    var delta = target - wall;
    if (delta <= 0) delta += 86400000;
    var candidate = new Date(now.getTime() + delta);
    for (var i = 0; i < 7; i++) {
      var w = nyParts(candidate);
      var day = new Date(Date.UTC(w.y, w.mo - 1, w.d)).getUTCDay();
      if (day >= 1 && day <= 5) return candidate;
      candidate = new Date(candidate.getTime() + 86400000);
    }
    return candidate;
  }

  function pad(n) { return String(n).padStart(2, '0'); }

  function tickSession() {
    var el = document.getElementById('ct-session-timer');
    if (!el) return;
    var now = new Date();
    var ms = nextCashOpen(now) - now;
    if (ms < 0) ms = 0;
    var total = Math.floor(ms / 1000);
    el.textContent = pad(Math.floor(total / 3600)) + ':' + pad(Math.floor((total % 3600) / 60)) + ':' + pad(total % 60);
  }

  /* ------------------------------------------------- form submit feedback -- */
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form.classList || !form.classList.contains('ct-form')) return;
    var btn = form.querySelector('.ct-submit');
    if (!btn) return;
    btn.setAttribute('aria-busy', 'true');
    btn.disabled = true;
    var label = btn.querySelector('.ct-submit-label');
    if (label) label.textContent = 'Sending…';
  });

  /* --------------------------------------------------------------- launch -- */
  function init() {
    menuEl = document.getElementById('ct-mobile-menu');
    tickSession();
    setInterval(tickSession, 1000);
    // Scroll the form into view after a redirect-back post.
    if (window.location.search.indexOf('ct_form=') !== -1) {
      var shell = document.querySelector('.ct-form-shell');
      if (shell) shell.scrollIntoView({ block: 'start', behavior: 'smooth' });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
