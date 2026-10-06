(function () {
  var sidebar = document.getElementById('residentSidebar');
  var toggle = document.getElementById('residentSidebarToggle');
  var backdrop = document.getElementById('residentSidebarBackdrop');

  if (!sidebar || !toggle || !backdrop) return;

  function setOpen(open, restoreFocus) {
    sidebar.classList.toggle('is-open', open);
    document.body.classList.toggle('resident-nav-open', open);
    backdrop.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close resident navigation' : 'Open resident navigation');
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
      var firstLink = sidebar.querySelector('.resident-sidebar__nav a');
      if (firstLink) firstLink.focus();
    } else if (restoreFocus !== false) {
      toggle.focus();
    }
  }

  toggle.addEventListener('click', function () {
    setOpen(!sidebar.classList.contains('is-open'));
  });
  backdrop.addEventListener('click', function () {
    setOpen(false);
  });
  sidebar.querySelectorAll('.resident-sidebar__nav a, .resident-sidebar__signout').forEach(function (link) {
    link.addEventListener('click', function () {
      setOpen(false);
    });
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && sidebar.classList.contains('is-open')) setOpen(false);
  });
  window.addEventListener('resize', function () {
    if (window.innerWidth > 960 && sidebar.classList.contains('is-open')) setOpen(false, false);
  });
})();
