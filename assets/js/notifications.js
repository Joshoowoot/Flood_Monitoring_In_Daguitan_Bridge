(function () {
  var cfg = window.DAGUITAN_NOTIFY || {};
  if (!cfg.listUrl) return;

  var wrap = document.getElementById('notifyWrap');
  var btn = document.getElementById('notifyBellBtn');
  var panel = document.getElementById('notifyPanel');
  var backdrop = document.getElementById('notifyBackdrop');
  var list = document.getElementById('notifyList');
  var badge = document.getElementById('notifyBadge');
  var markAll = document.getElementById('notifyMarkAll');
  var closeBtn = document.getElementById('notifyCloseBtn');
  var subtitle = document.getElementById('notifySubtitle');
  var open = false;
  var filter = 'all';
  var itemsCache = [];
  var loading = false;

  var typeMeta = {
    user_login:        { label: 'Sign-in', tone: 'info' },
    user_signup:       { label: 'New account', tone: 'success' },
    announcement:      { label: 'Advisory', tone: 'announce' },
    flood_alert:       { label: 'Flood alert', tone: 'critical' },
    admin_flood_alert: { label: 'Operations', tone: 'warning' },
  };

  function iconSvg(tone) {
    var paths = {
      info: '<path d="M12 16v-4M12 8h.01"/><circle cx="12" cy="12" r="9"/>',
      success: '<path d="M8 12l2.5 2.5L16 9"/><circle cx="12" cy="12" r="9"/>',
      announce: '<path d="M4 10v4M7 8v8M10 6v12M13 9v6M16 7v10M19 10v4"/>',
      critical: '<path d="M12 9v4M12 16h.01"/><path d="M10.3 4.7h3.4L21 19H3L10.3 4.7z"/>',
      warning: '<path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 19a2 2 0 0 0 4 0"/>',
    };
    return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">' + (paths[tone] || paths.info) + '</svg>';
  }

  function setBadge(count) {
    if (!badge || !btn) return;
    var n = Math.max(0, parseInt(count, 10) || 0);
    badge.hidden = n < 1;
    badge.textContent = n > 99 ? '99+' : String(n);
    btn.setAttribute('aria-label', n > 0 ? n + ' unread notifications' : 'Notifications, none unread');
    if (subtitle) {
      subtitle.textContent = n > 0 ? n + ' unread · tap to review' : "You're all caught up";
    }
    if (markAll) {
      markAll.disabled = n < 1;
    }
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function filteredItems() {
    if (filter === 'unread') {
      return itemsCache.filter(function (item) { return !item.is_read; });
    }
    return itemsCache;
  }

  function renderEmpty() {
    if (!list) return;
    list.innerHTML =
      '<li class="notify-empty">' +
        '<span class="notify-empty__icon" aria-hidden="true">' +
          '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>' +
        '</span>' +
        '<strong>' + (filter === 'unread' ? 'No unread notifications' : 'No notifications yet') + '</strong>' +
        '<p>' + (filter === 'unread' ? 'Switch to All to see earlier updates.' : 'Alerts and advisories will appear here.') + '</p>' +
      '</li>';
  }

  function renderSkeleton() {
    if (!list) return;
    list.innerHTML =
      '<li class="notify-skeleton" aria-hidden="true"><span></span><span></span><span></span></li>' +
      '<li class="notify-skeleton" aria-hidden="true"><span></span><span></span><span></span></li>' +
      '<li class="notify-skeleton" aria-hidden="true"><span></span><span></span><span></span></li>';
  }

  function renderItems() {
    if (!list) return;
    var items = filteredItems();
    if (!items.length) {
      renderEmpty();
      return;
    }
    list.innerHTML = items.map(function (item, idx) {
      var meta = typeMeta[item.type] || { label: 'Update', tone: 'info' };
      var cls = 'notify-item' + (item.is_read ? ' notify-item--read' : ' notify-item--unread');
      var link = item.link ? ' data-link="' + escapeHtml(item.link) + '"' : '';
      var chevron = item.link
        ? '<span class="notify-item__chev" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg></span>'
        : '';
      return (
        '<li class="' + cls + '"' + link + ' data-id="' + item.id + '" style="--notify-delay:' + Math.min(idx, 8) * 40 + 'ms">' +
          '<span class="notify-item__icon notify-item__icon--' + meta.tone + '">' + iconSvg(meta.tone) + '</span>' +
          '<div class="notify-item__content">' +
            '<div class="notify-item__row">' +
              '<span class="notify-item__tag">' + escapeHtml(meta.label) + '</span>' +
              '<time class="notify-item__time">' + escapeHtml(item.time_ago || '') + '</time>' +
            '</div>' +
            '<p class="notify-item__title">' + escapeHtml(item.title) + '</p>' +
            '<p class="notify-item__body">' + escapeHtml(item.body) + '</p>' +
          '</div>' +
          chevron +
        '</li>'
      );
    }).join('');
  }

  function fetchList(showSkeleton) {
    if (loading) return Promise.resolve();
    loading = true;
    if (showSkeleton) renderSkeleton();
    return fetch(cfg.listUrl, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.ok) return;
        itemsCache = data.items || [];
        setBadge(data.unread);
        renderItems();
      })
      .catch(function () {
        if (list) {
          list.innerHTML =
            '<li class="notify-empty notify-empty--error">' +
              '<strong>Could not load notifications</strong>' +
              '<p>Check your connection and try again.</p>' +
              '<button type="button" class="notify-retry" id="notifyRetry">Retry</button>' +
            '</li>';
          var retry = document.getElementById('notifyRetry');
          if (retry) retry.addEventListener('click', function () { fetchList(true); });
        }
      })
      .finally(function () { loading = false; });
  }

  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body,
    }).then(function (r) { return r.json(); });
  }

  function markRead(id) {
    if (!cfg.readUrl || !id) return Promise.resolve();
    return postJson(cfg.readUrl, 'id=' + encodeURIComponent(id));
  }

  function setOpen(next) {
    open = next;
    if (!panel || !btn) return;
    if (open) {
      panel.hidden = false;
      if (backdrop) backdrop.hidden = false;
      requestAnimationFrame(function () {
        panel.classList.add('notify-panel--open');
        if (backdrop) backdrop.classList.add('notify-backdrop--open');
        btn.classList.add('notify-bell--active');
      });
      fetchList(true);
    } else {
      panel.classList.remove('notify-panel--open');
      if (backdrop) backdrop.classList.remove('notify-backdrop--open');
      btn.classList.remove('notify-bell--active');
      window.setTimeout(function () {
        if (!open) {
          panel.hidden = true;
          if (backdrop) backdrop.hidden = true;
        }
      }, 220);
    }
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function setFilter(next) {
    filter = next;
    wrap.querySelectorAll('.notify-tabs__btn').forEach(function (tab) {
      var active = tab.getAttribute('data-filter') === filter;
      tab.classList.toggle('is-active', active);
      tab.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    renderItems();
  }

  if (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      setOpen(!open);
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', function () { setOpen(false); });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function () { setOpen(false); });
  }

  if (markAll) {
    markAll.addEventListener('click', function (e) {
      e.stopPropagation();
      if (!cfg.readAllUrl || markAll.disabled) return;
      markAll.classList.add('is-busy');
      postJson(cfg.readAllUrl, '').then(function () {
        return fetchList(false);
      }).finally(function () {
        markAll.classList.remove('is-busy');
      });
    });
  }

  wrap.querySelectorAll('.notify-tabs__btn').forEach(function (tab) {
    tab.addEventListener('click', function (e) {
      e.stopPropagation();
      setFilter(tab.getAttribute('data-filter') || 'all');
    });
  });

  if (list) {
    list.addEventListener('click', function (e) {
      var li = e.target.closest('.notify-item');
      if (!li) return;
      var id = li.getAttribute('data-id');
      var link = li.getAttribute('data-link');
      li.classList.add('notify-item--pressed');
      markRead(id).then(function () {
        var item = itemsCache.find(function (x) { return String(x.id) === String(id); });
        if (item) item.is_read = true;
        li.classList.remove('notify-item--unread');
        li.classList.add('notify-item--read');
        setBadge(itemsCache.filter(function (x) { return !x.is_read; }).length);
        window.setTimeout(function () {
          setOpen(false);
          if (link) window.location.href = link;
        }, link ? 180 : 0);
      });
    });
  }

  document.addEventListener('click', function (e) {
    if (open && wrap && !wrap.contains(e.target)) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && open) setOpen(false);
  });

  fetchList(false);
  setInterval(function () { fetchList(false); }, cfg.pollMs || 30000);
})();
