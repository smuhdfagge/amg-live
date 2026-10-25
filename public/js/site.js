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
// Resources: load a YouTube or Vimeo player only when the visitor presses play,
// and pause any other player that is already playing.
(function () {
  document.querySelectorAll('[data-play-embed]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var frame = btn.closest('[data-embed]');
      var src = frame.getAttribute('data-embed');
      var iframe = document.createElement('iframe');
      iframe.src = src + (src.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1';
      iframe.title = btn.getAttribute('aria-label') || 'Video';
      iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
      iframe.allowFullscreen = true;
      frame.replaceChildren(iframe);
      iframe.focus();
    });
  });
  document.addEventListener('play', function (e) {
    document.querySelectorAll('audio, video').forEach(function (m) { if (m !== e.target) m.pause(); });
  }, true);
})();
// Admin resource form: match the file picker and hint to the chosen type.
(function () {
  var form = document.querySelector('[data-resource-form]');
  if (!form) return;
  var type = form.querySelector('select[name="type"]');
  var input = form.querySelector('[data-media-input]');
  var hint = form.querySelector('[data-media-hint]');
  function sync() {
    var video = type.value === 'video';
    input.setAttribute('accept', video ? 'video/*' : 'audio/*');
    hint.textContent = hint.getAttribute(video ? 'data-video' : 'data-audio');
  }
  type.addEventListener('change', sync);
})();
