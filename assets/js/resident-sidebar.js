(function () {
  var sidebar = document.getElementById('residentSidebar');
  var toggle = document.getElementById('residentSidebarToggle');
  var closeButton = document.getElementById('residentSidebarClose');
  var backdrop = document.getElementById('residentSidebarBackdrop');

  if (!sidebar || !toggle || !backdrop) return;

  var main = sidebar.closest('main');
  if (main && backdrop.parentElement !== main) {
    main.insertBefore(backdrop, main.firstChild);
  }

  if (!closeButton) {
    closeButton = document.createElement('button');
    closeButton.className = 'resident-sidebar__close';
    closeButton.type = 'button';
    closeButton.id = 'residentSidebarClose';
    closeButton.setAttribute('aria-label', 'Close resident navigation');
    closeButton.textContent = '×';
    sidebar.insertBefore(closeButton, sidebar.firstChild);
  }

  var bottomNavLinks = document.querySelectorAll('.resident-dashboard .bottom-nav a[href^="#"]');
  var topbar = document.querySelector('.topbar');

  function updateHeaderHeight() {
    if (topbar) {
      document.documentElement.style.setProperty(
        '--resident-header-height',
        Math.ceil(topbar.getBoundingClientRect().height) + 'px'
      );
    }
  }

  function updateBottomNavigation() {
    var activeHash = window.location.hash || '#home';
    bottomNavLinks.forEach(function (link) {
      var active = link.getAttribute('href') === activeHash;
      link.classList.toggle('is-active', active);
      if (active) {
        link.setAttribute('aria-current', 'page');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  }

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
  if (closeButton) {
    closeButton.addEventListener('click', function () {
      setOpen(false);
    });
  }
  bottomNavLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      bottomNavLinks.forEach(function (item) {
        item.classList.remove('is-active');
        item.removeAttribute('aria-current');
      });
      link.classList.add('is-active');
      link.setAttribute('aria-current', 'page');
    });
  });
  window.addEventListener('hashchange', updateBottomNavigation);
  window.addEventListener('resize', updateHeaderHeight);
  updateHeaderHeight();
  updateBottomNavigation();
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
