/* Progressive enhancement only: every page works without this file. */
(function () {
  'use strict';

  function toggle(button, target, focusSelector) {
    var open = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(open));
    if (target.hasAttribute('hidden') || target.classList.contains('main-nav')) {
      if (target.classList.contains('main-nav')) {
        target.classList.toggle('is-open', open);
      } else {
        target.hidden = !open;
      }
    }
    if (open && focusSelector) {
      var field = target.querySelector(focusSelector);
      if (field) { field.focus(); }
    }
  }

  document.querySelectorAll('[data-toggle]').forEach(function (button) {
    var target = document.getElementById(button.getAttribute('aria-controls'));
    if (!target) { return; }
    button.addEventListener('click', function () {
      toggle(button, target, button.getAttribute('data-focus'));
    });
  });

  // Product gallery: thumbnails swap the main image.
  document.querySelectorAll('[data-gallery]').forEach(function (gallery) {
    var main = gallery.querySelector('[data-gallery-main]');
    gallery.querySelectorAll('[data-gallery-thumb]').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        main.src = thumb.getAttribute('data-src');
        gallery.querySelectorAll('[data-gallery-thumb]').forEach(function (other) {
          other.setAttribute('aria-current', String(other === thumb));
        });
      });
    });
  });

  // "Copy link" buttons on articles.
  document.querySelectorAll('[data-copy-link]').forEach(function (button) {
    button.addEventListener('click', function () {
      var label = button.querySelector('[data-copy-label]');
      var done = function () { if (label) { label.textContent = 'Đã sao chép'; } };
      if (navigator.clipboard) {
        navigator.clipboard.writeText(button.getAttribute('data-copy-link')).then(done);
      } else {
        window.prompt('Sao chép liên kết:', button.getAttribute('data-copy-link'));
      }
    });
  });

  // Tabs (WAI-ARIA pattern). Without JS all panels stay visible.
  document.querySelectorAll('[data-tabs]').forEach(function (root) {
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    var panels = tabs.map(function (tab) { return document.getElementById(tab.getAttribute('aria-controls')); });

    function select(index) {
      tabs.forEach(function (tab, i) {
        var active = i === index;
        tab.setAttribute('aria-selected', String(active));
        tab.tabIndex = active ? 0 : -1;
        panels[i].hidden = !active;
      });
    }

    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () { select(index); });
      tab.addEventListener('keydown', function (event) {
        var next = null;
        if (event.key === 'ArrowRight') { next = (index + 1) % tabs.length; }
        if (event.key === 'ArrowLeft') { next = (index - 1 + tabs.length) % tabs.length; }
        if (next !== null) { event.preventDefault(); select(next); tabs[next].focus(); }
      });
    });

    root.hidden = false;
    select(0);
  });
})();
