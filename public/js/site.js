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

  // Header pop-up menus: close on a click elsewhere or Escape.
  document.querySelectorAll('[data-nav-menu]').forEach(function (menu) {
    document.addEventListener('click', function (event) {
      if (menu.open && !menu.contains(event.target)) { menu.open = false; }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && menu.open) {
        menu.open = false;
        menu.querySelector('summary').focus();
      }
    });
  });

  // Image pop-up: links inside a [data-lightbox] group (a JSON list of image URLs) open it at their index.
  var lightbox = document.querySelector('[data-lightbox-dialog]');
  if (lightbox && typeof lightbox.showModal === 'function') {
    var lightboxImage = lightbox.querySelector('[data-lightbox-image]');
    var lightboxImages = [];
    var lightboxIndex = 0;
    var showImage = function (index) {
      lightboxIndex = (index + lightboxImages.length) % lightboxImages.length;
      lightboxImage.src = lightboxImages[lightboxIndex];
      lightbox.querySelector('[data-lightbox-counter]').textContent = lightboxImages.length > 1 ? (lightboxIndex + 1) + ' / ' + lightboxImages.length : '';
      lightbox.querySelectorAll('[data-lightbox-prev], [data-lightbox-next]').forEach(function (button) { button.hidden = lightboxImages.length < 2; });
    };

    document.querySelectorAll('[data-lightbox] [data-lightbox-index]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        var group = link.closest('[data-lightbox]');
        event.preventDefault();
        lightboxImages = JSON.parse(group.getAttribute('data-lightbox'));
        lightboxImage.alt = group.getAttribute('data-lightbox-title') || '';
        lightbox.querySelector('[data-lightbox-caption]').textContent = lightboxImage.alt;
        showImage(parseInt(link.getAttribute('data-lightbox-index'), 10) || 0);
        lightbox.showModal();
      });
    });

    lightbox.querySelector('[data-lightbox-prev]').addEventListener('click', function () { showImage(lightboxIndex - 1); });
    lightbox.querySelector('[data-lightbox-next]').addEventListener('click', function () { showImage(lightboxIndex + 1); });
    lightbox.querySelector('[data-lightbox-close]').addEventListener('click', function () { lightbox.close(); });
    // A click on the dark backdrop (the dialog itself, outside the image and buttons) closes it.
    lightbox.addEventListener('click', function (event) { if (event.target === lightbox) { lightbox.close(); } });
    lightbox.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') { showImage(lightboxIndex - 1); }
      if (event.key === 'ArrowRight') { showImage(lightboxIndex + 1); }
    });
    // Swipe on phones.
    var touchStartX = null;
    lightbox.addEventListener('touchstart', function (event) { touchStartX = event.touches[0].clientX; }, { passive: true });
    lightbox.addEventListener('touchend', function (event) {
      var distance = touchStartX === null ? 0 : event.changedTouches[0].clientX - touchStartX;
      if (Math.abs(distance) > 50 && lightboxImages.length > 1) { showImage(lightboxIndex + (distance < 0 ? 1 : -1)); }
      touchStartX = null;
    });
  }

  // Product group sidebar: folded on phones so the products come first; the summary opens it.
  if (window.matchMedia('(max-width: 960px)').matches) {
    document.querySelectorAll('[data-tree]').forEach(function (tree) { tree.open = false; });
  }
  document.querySelectorAll('[data-tree] > summary').forEach(function (summary) {
    summary.addEventListener('click', function (event) {
      if (window.matchMedia('(min-width: 961px)').matches) { event.preventDefault(); }
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
