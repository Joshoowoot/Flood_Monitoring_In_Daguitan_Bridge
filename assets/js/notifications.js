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
  var soundButton = document.getElementById('notifySoundButton');
  var soundLabel = document.getElementById('notifySoundLabel');
  var closeBtn = document.getElementById('notifyCloseBtn');
  var subtitle = document.getElementById('notifySubtitle');
  var actionStatus = document.getElementById('notifyActionStatus');
  var pushStatus = document.getElementById('notifyPushStatus');
  var pushButton = document.getElementById('notifyPushButton');
  var open = false;
  var filter = 'all';
  var itemsCache = [];
  var loading = false;
  var knownNotificationIds = Object.create(null);
  var notificationListInitialized = false;
  var alertedNotificationIds = Object.create(null);
  var alertAudioContext = null;
  var soundPreferenceKey = 'daguitanNotificationSound';
  var soundEnabled = true;

  try {
    soundEnabled = window.localStorage.getItem(soundPreferenceKey) !== 'off';
  } catch (error) {
    console.warn('Notification sound preference could not be loaded.', error);
  }

  function updateSoundButton() {
    if (!soundButton) return;
    soundButton.setAttribute('aria-pressed', soundEnabled ? 'true' : 'false');
    soundButton.setAttribute('aria-label', soundEnabled ? 'Turn in-page notification sound off' : 'Turn in-page notification sound on');
    if (soundLabel) soundLabel.textContent = soundEnabled ? 'Page sound on' : 'Page sound off';
  }

  function playAlertTone() {
    var AudioContextConstructor = window.AudioContext || window.webkitAudioContext;
    if (!soundEnabled || !AudioContextConstructor) return;

    try {
      if (!alertAudioContext) alertAudioContext = new AudioContextConstructor();
      var context = alertAudioContext;
      var playChime = function () {
        if (context.state !== 'running') return;
        [784, 988, 1175].forEach(function (frequency, index) {
          var start = context.currentTime + 0.02 + index * 0.2;
          var oscillator = context.createOscillator();
          var gain = context.createGain();
          oscillator.type = 'sine';
          oscillator.frequency.setValueAtTime(frequency, start);
          gain.gain.setValueAtTime(0.0001, start);
          gain.gain.exponentialRampToValueAtTime(0.18, start + 0.025);
          gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.17);
          oscillator.connect(gain);
          gain.connect(context.destination);
          oscillator.start(start);
          oscillator.stop(start + 0.19);
        });
      };

      if (context.state === 'running') {
        playChime();
      } else {
        context.resume().then(playChime).catch(function (error) {
          console.warn('Notification sound could not be played.', error);
        });
      }
    } catch (error) {
      console.warn('Notification sound could not be played.', error);
    }
  }

  function alertNewNotification(id, playSound) {
    var key = String(id || '');
    if (!key || alertedNotificationIds[key]) return false;
    alertedNotificationIds[key] = true;
    if (playSound !== false && !document.hidden) playAlertTone();
    return true;
  }

  function unlockAlertAudio() {
    if (!soundEnabled || !window.AudioContext && !window.webkitAudioContext) return;
    try {
      if (!alertAudioContext) {
        var AudioContextConstructor = window.AudioContext || window.webkitAudioContext;
        alertAudioContext = new AudioContextConstructor();
      }
      if (alertAudioContext.state === 'suspended') {
        alertAudioContext.resume().catch(function (error) {
          console.warn('Notification audio could not be enabled.', error);
        });
      }
    } catch (error) {
      console.warn('Notification audio could not be enabled.', error);
    }
  }

  updateSoundButton();

  var typeMeta = {
    user_login:        { label: 'Sign-in', tone: 'info' },
    user_signup:       { label: 'New account', tone: 'success' },
    announcement:      { label: 'Advisory', tone: 'announce' },
    flood_alert:       { label: 'Flood alert', tone: 'critical' },
    admin_flood_alert: { label: 'Operations', tone: 'warning' },
    community_report:  { label: 'Resident report', tone: 'warning' },
    report_update:     { label: 'Report update', tone: 'info' },
    family_plan:       { label: 'Family plan shared', tone: 'success' },
    family_plan_revoked: { label: 'Family plan access', tone: 'info' },
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
    var items = filteredItems().slice().sort(function (left, right) {
      var leftUrgent = left.type === 'flood_alert' || left.type === 'admin_flood_alert';
      var rightUrgent = right.type === 'flood_alert' || right.type === 'admin_flood_alert';
      return Number(rightUrgent) - Number(leftUrgent);
    });
    if (!items.length) {
      renderEmpty();
      return;
    }
    list.innerHTML = items.map(function (item, idx) {
      var meta = typeMeta[item.type] || { label: 'Update', tone: 'info' };
      var cls = 'notify-item' + (item.is_read ? ' notify-item--read' : ' notify-item--unread');
      var link = item.link ? ' data-link="' + escapeHtml(item.link) + '"' : '';
      var action = item.link ? '<span class="notify-item__action">' + (item.type === 'flood_alert' ? 'View urgent alert' : 'Open update') + '</span>' : '';
      var chevron = item.link
        ? '<span class="notify-item__chev" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg></span>'
        : '';
      return (
        '<li class="' + cls + '"' + link + ' data-id="' + item.id + '"' + (item.link ? ' role="link" tabindex="0" aria-label="' + escapeHtml(item.title + ', ' + (item.type === 'flood_alert' ? 'view urgent alert' : 'open update')) + '"' : '') + ' style="--notify-delay:' + Math.min(idx, 8) * 40 + 'ms">' +
          '<span class="notify-item__icon notify-item__icon--' + meta.tone + '">' + iconSvg(meta.tone) + '</span>' +
          '<div class="notify-item__content">' +
            '<div class="notify-item__row">' +
              '<span class="notify-item__tag">' + escapeHtml(meta.label) + '</span>' +
              '<time class="notify-item__time">' + escapeHtml(item.time_ago || '') + '</time>' +
            '</div>' +
            '<p class="notify-item__title">' + escapeHtml(item.title) + '</p>' +
            '<p class="notify-item__body">' + escapeHtml(item.body) + '</p>' +
            action +
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
    return fetch(cfg.listUrl, { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) {
        if (!r.ok) throw new Error('Notification service returned HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        if (!data || !data.ok) throw new Error('Notification response was not successful');
        var nextItems = Array.isArray(data.items) ? data.items : [];
        var freshUnread = [];
        nextItems.forEach(function (item) {
          var key = String(item.id || '');
          if (!key) return;
          if (notificationListInitialized && !knownNotificationIds[key] && !item.is_read) {
            freshUnread.push(item.id);
          }
          knownNotificationIds[key] = true;
        });
        notificationListInitialized = true;
        itemsCache = nextItems;
        var shouldPlayAlert = false;
        freshUnread.forEach(function (id) {
          shouldPlayAlert = alertNewNotification(id, false) || shouldPlayAlert;
        });
        if (shouldPlayAlert && !document.hidden) playAlertTone();
        setBadge(data.unread);
        renderItems();
      })
      .catch(function (error) {
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
        console.warn('Notifications could not be loaded.', error);
      })
      .finally(function () { loading = false; });
  }

  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body,
    }).then(function (r) {
      if (!r.ok) throw new Error('Notification service returned HTTP ' + r.status);
      return r.json();
    });
  }

  function pushPost(body) {
    return fetch(cfg.pushSubscriptionUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok || !data.ok) {
          var message = data.error === 'push_not_configured'
            ? 'Push alerts are not configured on this server.'
            : 'Push subscription could not be saved. Please try again.';
          throw new Error(message);
        }
        return data;
      });
    });
  }

  function setPushStatus(message, buttonLabel, showButton) {
    if (pushStatus) pushStatus.textContent = message;
    if (pushButton) {
      pushButton.textContent = buttonLabel || '';
      pushButton.hidden = !showButton;
      pushButton.disabled = false;
      pushButton.classList.remove('is-busy');
    }
  }

  function applicationServerKey(value) {
    var padding = '='.repeat((4 - value.length % 4) % 4);
    var binary = window.atob((value + padding).replace(/-/g, '+').replace(/_/g, '/'));
    var bytes = new Uint8Array(binary.length);
    for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
    return bytes;
  }

  function getPushRegistration() {
    if (!navigator.serviceWorker || !window.PushManager) {
      return Promise.reject(new Error('This browser does not support web push alerts.'));
    }
    if (cfg.serviceWorkerUrl) {
      return navigator.serviceWorker.register(cfg.serviceWorkerUrl).then(function () {
        return navigator.serviceWorker.ready;
      });
    }
    return navigator.serviceWorker.ready;
  }

  function saveCurrentSubscription(subscription) {
    return pushPost({ action: 'subscribe', subscription: subscription.toJSON() })
      .then(function () {
        setPushStatus('Push alerts are on for this device.', 'Turn off', true);
      });
  }

  function refreshPushStatus() {
    if (!pushStatus || !pushButton) return;
    if (!('Notification' in window) || !navigator.serviceWorker || !window.PushManager) {
      setPushStatus('This browser does not support push alerts. In-app notifications remain available.', '', false);
      return;
    }
    if (!cfg.pushStatusUrl || !cfg.pushSubscriptionUrl) {
      setPushStatus('Push alerts are unavailable. In-app notifications remain available.', '', false);
      return;
    }

    fetch(cfg.pushStatusUrl, { credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) throw new Error('Push status returned HTTP ' + response.status);
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !data.push || !data.push.configured) {
          setPushStatus('Push delivery is not configured on this server. In-app notifications remain available.', '', false);
          return;
        }
        if (Notification.permission === 'denied') {
          setPushStatus('Notifications are blocked. Open this site’s browser settings, set Notifications to Allow, then check again.', 'Check again', true);
          return;
        }
        return getPushRegistration().then(function (registration) {
          return registration.pushManager.getSubscription().then(function (subscription) {
            if (subscription) return saveCurrentSubscription(subscription);
            if (Notification.permission === 'granted') {
              setPushStatus('Push alerts are allowed but are not enabled on this device.', 'Enable on this device', true);
              return;
            }
            setPushStatus('Get flood alerts even when this page is closed.', 'Enable on this device', true);
          });
        });
      })
      .catch(function (error) {
        setPushStatus('Push alerts could not be checked. In-app notifications remain available.', 'Try again', true);
        console.warn('Push notification status could not be loaded.', error);
      });
  }

  function enablePush() {
    if (!pushButton || pushButton.disabled) return;
    pushButton.disabled = true;
    pushButton.classList.add('is-busy');
    pushButton.textContent = 'Working…';

    var permission = Notification.permission === 'granted'
      ? Promise.resolve('granted')
      : Notification.requestPermission();
    permission.then(function (result) {
      if (result !== 'granted') {
        setPushStatus(result === 'denied'
          ? 'Notifications are blocked. Open this site’s browser settings, set Notifications to Allow, then check again.'
          : 'Push alerts were not enabled. You can choose to enable them later.',
        result === 'denied' ? 'Check again' : 'Enable on this device', true);
        return;
      }

      return fetch(cfg.pushStatusUrl, { credentials: 'same-origin' })
        .then(function (response) {
          if (!response.ok) throw new Error('Push status returned HTTP ' + response.status);
          return response.json();
        })
        .then(function (data) {
          if (!data || !data.ok || !data.push || !data.push.configured || !data.push.publicKey) {
            throw new Error('Push delivery is not configured on this server.');
          }
          return getPushRegistration().then(function (registration) {
            return registration.pushManager.getSubscription().then(function (subscription) {
              return subscription || registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey(data.push.publicKey),
              });
            }).then(function (subscription) {
              return saveCurrentSubscription(subscription);
            });
          });
        });
    }).catch(function (error) {
      setPushStatus(error.message || 'Push alerts could not be enabled. Please try again.', 'Try again', true);
      console.warn('Push alerts could not be enabled.', error);
    }).finally(function () {
      if (pushButton) {
        pushButton.disabled = false;
        pushButton.classList.remove('is-busy');
      }
    });
  }

  function disablePush() {
    if (!pushButton || pushButton.disabled) return;
    pushButton.disabled = true;
    pushButton.classList.add('is-busy');
    pushButton.textContent = 'Turning off…';
    getPushRegistration().then(function (registration) {
      return registration.pushManager.getSubscription().then(function (subscription) {
        if (!subscription) {
          setPushStatus('Push alerts are off for this device.', 'Enable on this device', true);
          return;
        }
        return pushPost({ action: 'unsubscribe', endpoint: subscription.endpoint })
          .then(function () { return subscription.unsubscribe(); })
          .then(function (removed) {
            if (!removed) throw new Error('The browser could not turn off this subscription.');
            setPushStatus('Push alerts are off for this device.', 'Enable on this device', true);
          });
      });
    }).catch(function (error) {
      setPushStatus(error.message || 'Push alerts could not be turned off. Please try again.', 'Try again', true);
      console.warn('Push alerts could not be turned off.', error);
    }).finally(function () {
      if (pushButton) {
        pushButton.disabled = false;
        pushButton.classList.remove('is-busy');
      }
    });
  }

  if (pushButton) {
    pushButton.addEventListener('click', function () {
      var label = pushButton.textContent.toLowerCase();
      if (label === 'check again') {
        refreshPushStatus();
      } else if (label.indexOf('turn off') !== -1) {
        disablePush();
      } else {
        enablePush();
      }
    });
    refreshPushStatus();
  }

  function markRead(id) {
    if (!cfg.readUrl || !id) return Promise.resolve();
    return postJson(cfg.readUrl, 'id=' + encodeURIComponent(id)).then(function (data) {
      if (!data || !data.ok) throw new Error('Notification could not be marked as read');
      return data;
    });
  }

  function setOpen(next) {
    open = next;
    if (!panel || !btn) return;
    if (open) {
      if (actionStatus) {
        actionStatus.hidden = true;
        actionStatus.textContent = '';
      }
      document.body.classList.add('resident-notifications-open');
      panel.hidden = false;
      if (backdrop) backdrop.hidden = false;
      requestAnimationFrame(function () {
        panel.classList.add('notify-panel--open');
        if (backdrop) backdrop.classList.add('notify-backdrop--open');
        btn.classList.add('notify-bell--active');
        if (closeBtn && window.matchMedia('(max-width: 640px)').matches) closeBtn.focus();
      });
      fetchList(true);
    } else {
      document.body.classList.remove('resident-notifications-open');
      panel.classList.remove('notify-panel--open');
      if (backdrop) backdrop.classList.remove('notify-backdrop--open');
      btn.classList.remove('notify-bell--active');
      window.setTimeout(function () {
        if (!open) {
          panel.hidden = true;
          if (backdrop) backdrop.hidden = true;
          btn.focus();
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
      unlockAlertAudio();
      e.stopPropagation();
      setOpen(!open);
    });
  }

  if (soundButton) {
    soundButton.addEventListener('click', function () {
      soundEnabled = !soundEnabled;
      try {
        window.localStorage.setItem(soundPreferenceKey, soundEnabled ? 'on' : 'off');
      } catch (error) {
        console.warn('Notification sound preference could not be saved.', error);
      }
      updateSoundButton();
      if (soundEnabled) {
        unlockAlertAudio();
        playAlertTone();
      }
    });
  }

  document.addEventListener('pointerdown', unlockAlertAudio, { once: true, capture: true });
  document.addEventListener('keydown', unlockAlertAudio, { once: true, capture: true });

  if (navigator.serviceWorker) {
    navigator.serviceWorker.addEventListener('message', function (event) {
      var message = event.data;
      if (!message || message.type !== 'daguitan:push' || !message.notificationId) return;
      alertNewNotification(message.notificationId);
      fetchList(false);
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
      postJson(cfg.readAllUrl, '').then(function (data) {
        if (!data || !data.ok) throw new Error('Notifications could not be marked as read');
        return fetchList(false);
      }).catch(function (error) {
        if (actionStatus) {
          actionStatus.hidden = false;
          actionStatus.textContent = 'Could not mark notifications as read. Please try again.';
        }
        console.warn('Notifications could not be marked as read.', error);
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
      }).catch(function (error) {
        li.classList.remove('notify-item--pressed');
        if (actionStatus) {
          actionStatus.hidden = false;
          actionStatus.textContent = 'Could not update this notification. Please try again.';
        }
        console.warn('Notification could not be marked as read.', error);
      });
    });
    list.addEventListener('keydown', function (event) {
      var li = event.target.closest('.notify-item[role="link"]');
      if (li && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        li.click();
      }
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

  if (soundButton) soundButton.addEventListener('pointerdown', unlockAlertAudio, { capture: true });
  fetchList(false);
  setInterval(function () {
    if (!document.hidden && navigator.onLine) fetchList(false);
  }, cfg.pollMs || 5000);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden && navigator.onLine) fetchList(false);
  });
  window.addEventListener('focus', function () {
    if (navigator.onLine) fetchList(false);
  });
  window.addEventListener('online', function () { fetchList(false); });
})();
