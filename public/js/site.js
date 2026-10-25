(function () {
  var toggle = document.querySelector('[data-nav-toggle]');
  var nav = document.getElementById('main-nav');
  if (toggle && nav) {
    var openIcon = toggle.querySelector('[data-icon-open]');
    var closeIcon = toggle.querySelector('[data-icon-close]');
    var label = toggle.querySelector('.sr-only');
    function setOpen(open) {
      nav.classList.toggle('is-open', open);
      document.body.classList.toggle('nav-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (openIcon) open ? openIcon.setAttribute('hidden', '') : openIcon.removeAttribute('hidden');
      if (closeIcon) open ? closeIcon.removeAttribute('hidden') : closeIcon.setAttribute('hidden', '');
      if (label) label.textContent = open ? 'Close menu' : 'Open menu';
    }
    toggle.addEventListener('click', function () { setOpen(!nav.classList.contains('is-open')); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && nav.classList.contains('is-open')) { setOpen(false); toggle.focus(); } });
    window.addEventListener('resize', function () { if (window.innerWidth > 1180) setOpen(false); });
  }

  // Confirm before destructive admin actions
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });
})();
(function () {
  var t = document.querySelector('[data-admin-toggle]');
  var s = document.querySelector('.admin-side');
  if (t && s) t.addEventListener('click', function () { s.classList.toggle('is-open'); });
})();
