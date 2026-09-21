(function () {
  var cfg = window.DAGUITAN_ADMIN || {};
  var canvas = document.getElementById('adminWaterChart');

  function drawChart() {
    if (!canvas || !cfg.chart || !cfg.chart.points || !cfg.chart.points.length) return;
    var ctx = canvas.getContext('2d');
    var dpr = window.devicePixelRatio || 1;
    var rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    var w = rect.width;
    var h = rect.height;
    var pad = { t: 16, r: 16, b: 28, l: 42 };
    var yellow = parseFloat(cfg.chart.yellow || cfg.thresholds.yellow || 1.5);
    var red = parseFloat(cfg.chart.red || cfg.thresholds.red || 2.5);
    var maxY = Math.max(red * 1.15, 3);
    var points = cfg.chart.points;

    function yScale(v) {
      var inner = h - pad.t - pad.b;
      return pad.t + inner - (v / maxY) * inner;
    }

    ctx.clearRect(0, 0, w, h);
    ctx.fillStyle = 'rgba(255,255,255,0.35)';
    ctx.fillRect(pad.l, pad.t, w - pad.l - pad.r, h - pad.t - pad.b);

    [
      { to: yellow, color: 'rgba(18, 122, 60, 0.12)' },
      { from: yellow, to: red, color: 'rgba(154, 116, 0, 0.14)' },
      { from: red, to: maxY, color: 'rgba(177, 13, 30, 0.14)' }
    ].forEach(function (z) {
      var y1 = yScale(z.from || 0);
      var y2 = yScale(z.to != null ? z.to : maxY);
      ctx.fillStyle = z.color;
      ctx.fillRect(pad.l, Math.min(y1, y2), w - pad.l - pad.r, Math.abs(y2 - y1));
    });

    ctx.strokeStyle = 'rgba(155, 18, 36, 0.25)';
    ctx.setLineDash([4, 4]);
    [yellow, red].forEach(function (lv) {
      var y = yScale(lv);
      ctx.beginPath();
      ctx.moveTo(pad.l, y);
      ctx.lineTo(w - pad.r, y);
      ctx.stroke();
    });
    ctx.setLineDash([]);

    var innerW = w - pad.l - pad.r;
    ctx.strokeStyle = '#9b1224';
    ctx.lineWidth = 2;
    ctx.beginPath();
    points.forEach(function (p, i) {
      var x = pad.l + (i / Math.max(1, points.length - 1)) * innerW;
      var y = yScale(p.water_level_m);
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    ctx.stroke();

    ctx.fillStyle = '#6b5558';
    ctx.font = '11px Times New Roman, serif';
    ctx.fillText('0 m', 6, yScale(0) + 4);
    ctx.fillText(yellow.toFixed(1) + ' m', 6, yScale(yellow) + 4);
    ctx.fillText(red.toFixed(1) + ' m', 6, yScale(red) + 4);
  }

  function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function updateWarningBadge(el, m) {
    if (!el) return;
    if (el.classList && el.classList.contains('status-badge')) {
      el.textContent = m.warning_label;
      el.className = 'status-badge status-badge--' + m.warning_level;
      return;
    }
    el.textContent = m.warning_label;
  }

  function poll() {
    if (!cfg.statusUrl) return;
    fetch(cfg.statusUrl, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.monitor) return;
        var m = data.monitor;
        var waterEl = document.getElementById('admWater');
        if (waterEl) {
          if (waterEl.querySelector('span')) {
            waterEl.childNodes[0].textContent = Number(m.water_level_m).toFixed(2) + ' ';
          } else {
            waterEl.textContent = Number(m.water_level_m).toFixed(2) + ' m';
          }
        }
        updateWarningBadge(document.getElementById('admWarning'), m);
        setText('admRate', (m.rate_cm_min > 0 ? '+' : '') + Number(m.rate_cm_min).toFixed(2) + ' cm/min');
        setText('admTrend', m.trend_label);
        setText('admUpdated', m.last_updated);
        setText('admEtt', m.ett_label || '—');
        setText('admSensorLabel', m.sensor_label);
        if (data.sync && data.sync.internet_label) {
          setText('admInternet', data.sync.internet_label);
          setText('admCloudSync', data.sync.internet_label);
        }
        if (m.age_seconds != null) {
          setText('admPacketAge', m.age_seconds + ' s');
        }
        if (data.sync && data.sync.pending_total != null) {
          setText('admPendingSync', String(data.sync.pending_total));
        }
        var sensor = document.getElementById('admSensor');
        if (sensor) {
          sensor.classList.toggle('metric--online', m.sensor_status === 'online');
          sensor.classList.toggle('metric--offline', m.sensor_status !== 'online');
        }
        document.querySelectorAll('[data-adm-sensor-pill]').forEach(function (pill) {
          var key = pill.getAttribute('data-adm-sensor-pill');
          var online = m.sensor_status === 'online';
          if (key === 'esp32' || key === 'ultrasonic') {
            pill.textContent = online ? 'Online' : 'Offline';
            pill.className = 'admin-pill admin-pill--' + (online ? 'online' : 'offline');
          }
        });
        var recentBody = document.getElementById('admRecentBody');
        if (recentBody && m.water_level_m != null && m.last_updated) {
          var first = recentBody.querySelector('tr');
          var level = Number(m.water_level_m).toFixed(3) + ' m';
          var warnHtml = document.getElementById('admWarning');
          var warnLabel = warnHtml ? warnHtml.textContent : m.warning_label;
          var warnLevel = m.warning_level || 'green';
          var trend = m.trend_label || '—';
          if (first) {
            var cells = first.querySelectorAll('td');
            if (cells.length >= 4 && cells[1].textContent.indexOf(level) !== 0) {
              var tr = document.createElement('tr');
              tr.innerHTML =
                '<td>' + m.last_updated + '</td>' +
                '<td>' + level + '</td>' +
                '<td><span class="status-badge status-badge--' + warnLevel + '">' + warnLabel + '</span></td>' +
                '<td>' + trend + '</td>';
              recentBody.insertBefore(tr, first);
              while (recentBody.rows.length > 12) {
                recentBody.removeChild(recentBody.lastElementChild);
              }
            }
          }
        }
        if (cfg.chart && cfg.chart.points && m.water_level_m != null) {
          var last = cfg.chart.points[cfg.chart.points.length - 1];
          var now = Math.floor(Date.now() / 1000);
          if (!last || Math.abs(last.water_level_m - m.water_level_m) > 0.0001 || now - last.ts > 8) {
            cfg.chart.points.push({ ts: now, water_level_m: Number(m.water_level_m) });
            if (cfg.chart.points.length > 120) cfg.chart.points.shift();
            drawChart();
          }
        }
      })
      .catch(function () {});
  }

  drawChart();
  window.addEventListener('resize', drawChart);
  if (cfg.statusUrl) {
    poll();
    setInterval(poll, cfg.pollMs || 5000);
  }

  var refreshBtn = document.getElementById('admRefreshNow');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', poll);
  }

  var toggle = document.getElementById('adminNavToggle');
  var sidebar = document.getElementById('adminSidebar');
  var backdrop = document.getElementById('adminBackdrop');

  function setNavOpen(open) {
    if (!sidebar) return;
    sidebar.classList.toggle('is-open', open);
    document.body.classList.toggle('admin-nav-open', open);
    if (toggle) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }
    if (backdrop) backdrop.hidden = !open;
  }

  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      setNavOpen(!sidebar.classList.contains('is-open'));
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', function () {
      setNavOpen(false);
    });
  }

  if (sidebar) {
    sidebar.querySelectorAll('.admin-nav__link').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.matchMedia('(max-width: 960px)').matches) {
          setNavOpen(false);
        }
      });
    });
  }

  window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 961px)').matches) {
      setNavOpen(false);
    }
  });
})();
