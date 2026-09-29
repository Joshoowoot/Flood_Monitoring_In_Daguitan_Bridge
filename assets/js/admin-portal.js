(function () {
  var cfg = window.DAGUITAN_ADMIN || {};
  var canvas = document.getElementById('adminWaterChart');

  function drawChart() {
    if (!canvas || !cfg.chart || !cfg.chart.points) return;
    var windowSeconds = Number(canvas.getAttribute('data-window-seconds')) || 3600;
    var now = Math.floor(Date.now() / 1000);
    var start = now - windowSeconds;
    var points = cfg.chart.points.filter(function (point) {
      return Number(point.ts) >= start && Number(point.ts) <= now && Number.isFinite(Number(point.water_level_m));
    });
    var empty = document.getElementById('admChartEmpty');
    canvas.hidden = points.length === 0;
    if (empty) empty.hidden = points.length > 0;
    if (!points.length) return;
    var ctx = canvas.getContext('2d');
    var dpr = window.devicePixelRatio || 1;
    var rect = canvas.getBoundingClientRect();
    if (!rect.width || !rect.height) return;
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    var w = rect.width;
    var h = rect.height;
    var pad = { t: 16, r: 16, b: 28, l: 42 };
    var yellow = parseFloat(cfg.chart.yellow || cfg.thresholds.yellow || 1.5);
    var red = parseFloat(cfg.chart.red || cfg.thresholds.red || 2.5);
    var maxY = Math.max(red * 1.15, 3);
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

    ctx.strokeStyle = 'rgba(107, 85, 88, 0.12)';
    ctx.lineWidth = 1;
    [0, maxY / 2, maxY].forEach(function (lv) {
      var y = yScale(lv);
      ctx.beginPath();
      ctx.moveTo(pad.l, y);
      ctx.lineTo(w - pad.r, y);
      ctx.stroke();
    });

    ctx.fillStyle = 'rgba(107, 85, 88, 0.78)';
    ctx.font = '700 10px Times New Roman, serif';
    ctx.fillText('CRITICAL', w - pad.r - 48, yScale(red) - 6);
    ctx.fillText('MONITOR', w - pad.r - 43, yScale(yellow) - 6);
    ctx.fillText('SAFE', w - pad.r - 25, yScale(yellow) + 15);

    var innerW = w - pad.l - pad.r;
    function xScale(ts) {
      return pad.l + ((ts - start) / windowSeconds) * innerW;
    }
    var lastX = xScale(Number(points[points.length - 1].ts));
    var lastY = yScale(points[points.length - 1].water_level_m);
    ctx.beginPath();
    points.forEach(function (p, i) {
      var x = xScale(Number(p.ts));
      var y = yScale(p.water_level_m);
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    ctx.lineTo(lastX, yScale(0));
    ctx.lineTo(xScale(Number(points[0].ts)), yScale(0));
    ctx.closePath();
    ctx.fillStyle = 'rgba(155, 18, 36, 0.08)';
    ctx.fill();

    ctx.strokeStyle = '#9b1224';
    ctx.lineWidth = 2;
    ctx.beginPath();
    points.forEach(function (p, i) {
      var x = xScale(Number(p.ts));
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
    ctx.fillText('60 min ago', pad.l, h - 7);
    ctx.textAlign = 'center';
    ctx.fillText('30 min ago', pad.l + innerW / 2, h - 7);
    ctx.textAlign = 'right';
    ctx.fillText('Now', w - pad.r, h - 7);
    ctx.textAlign = 'left';

    ctx.beginPath();
    ctx.arc(lastX, lastY, 4, 0, Math.PI * 2);
    ctx.fillStyle = '#9b1224';
    ctx.fill();
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 2;
    ctx.stroke();
    ctx.fillStyle = '#1a1012';
    ctx.font = '700 12px Times New Roman, serif';
    ctx.fillText(Number(points[points.length - 1].water_level_m).toFixed(2) + ' m', Math.max(pad.l, lastX - 20), Math.max(14, lastY - 10));
  }

  function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text;
  }

  function updateFreshness(m) {
    var hero = document.querySelector('.admin-live-hero');
    var fresh = document.getElementById('admFreshness');
    var label = document.getElementById('admReadingLabel');
    var isLive = m.sensor_status === 'online';
    var isWaiting = m.sensor_status === 'waiting';
    var age = m.age_seconds == null ? null : Math.max(0, Number(m.age_seconds));
    var ageLabel = age == null ? 'No telemetry received' : (age < 60 ? Math.floor(age) + ' sec ago' : Math.floor(age / 60) + ' min ago');
    if (hero) hero.classList.toggle('is-stale', !isLive);
    if (label) label.textContent = isLive ? 'Current reading' : (isWaiting ? 'Waiting for reading' : 'Last known reading');
    if (fresh) {
      fresh.className = 'admin-freshness admin-freshness--' + (isLive ? 'live' : (isWaiting ? 'waiting' : 'stale'));
      fresh.textContent = isLive ? 'Live telemetry · packet received ' + ageLabel : (isWaiting ? 'Waiting for first telemetry' : 'Station offline · last packet ' + ageLabel);
    }
  }

  function updateThreshold(m) {
    var level = Number(m.water_level_m);
    var yellow = Number(m.threshold_yellow_m || cfg.thresholds.yellow || 1.5);
    var red = Number(m.threshold_red_m || cfg.thresholds.red || 2.5);
    var context = m.sensor_status === 'waiting' ? 'Waiting for telemetry' : (level >= red ? 'Critical threshold reached' : (level >= yellow ? (red - level).toFixed(2) + ' m to critical threshold' : (yellow - level).toFixed(2) + ' m to monitor threshold'));
    var meter = document.getElementById('admThresholdMeter');
    var fill = document.getElementById('admThresholdFill');
    var warning = m.warning_level || 'green';
    setText('admThresholdContext', context);
    if (meter) meter.setAttribute('aria-valuenow', level.toFixed(2));
    if (fill) {
      fill.style.width = Math.max(0, Math.min(100, level / Math.max(red, 0.01) * 100)) + '%';
      fill.className = 'admin-threshold-track__fill admin-threshold-track__fill--' + warning;
    }
  }

  function appendReading(m) {
    if (!cfg.chart || !cfg.chart.points || m.sensor_status !== 'online' || !m.last_updated_iso) return;
    var ts = Math.floor(Date.parse(m.last_updated_iso) / 1000);
    if (!Number.isFinite(ts)) return;
    var points = cfg.chart.points;
    var last = points.length ? points[points.length - 1] : null;
    if (last && Number(last.ts) >= ts) return;
    points.push({ ts: ts, water_level_m: Number(m.water_level_m) });
    var cutoff = Math.floor(Date.now() / 1000) - (Number(canvas && canvas.getAttribute('data-window-seconds')) || 3600);
    cfg.chart.points = points.filter(function (point) { return Number(point.ts) >= cutoff; });
    cfg.chart.points = cfg.chart.points.slice(-(Number(cfg.chart.max_points) || 800));
    drawChart();
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
        updateFreshness(m);
        updateThreshold(m);
        var waterEl = document.getElementById('admWater');
        if (waterEl) {
          var waterText = m.sensor_status === 'waiting' ? '— ' : Number(m.water_level_m).toFixed(2) + ' ';
          if (waterEl.querySelector('span')) {
            waterEl.childNodes[0].textContent = waterText;
          } else {
            waterEl.textContent = waterText + 'm';
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
          var internetPill = document.getElementById('admInternetPill');
          if (internetPill) {
            var internetOnline = Boolean(data.sync.internet);
            internetPill.textContent = internetOnline ? 'Online' : 'Offline';
            internetPill.className = 'admin-pill admin-pill--' + (internetOnline ? 'online' : 'offline');
          }
          setText('admInternetDetail', data.sync.internet_label);
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
        document.querySelectorAll('[data-infra-key]').forEach(function (card) {
          var key = card.getAttribute('data-infra-key');
          var status = card.querySelector('.admin-sensor-status');
          var detail = card.querySelector('p:not(.admin-sensor-status)');
          if (!status || !detail) return;
          if (key === 'power') {
            status.textContent = m.sensor_status === 'online' ? 'Online' : 'Offline';
            status.className = 'admin-sensor-status admin-sensor-status--' + (m.sensor_status === 'online' ? 'online' : 'offline');
            detail.textContent = m.sensor_status === 'online' ? 'Telemetry connected; source not reported' : 'No telemetry available';
          } else if (key === 'solar' || key === 'battery') {
            status.textContent = 'Telemetry unavailable';
            status.className = 'admin-sensor-status admin-sensor-status--warning';
            detail.textContent = 'Waiting for ESP power telemetry';
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
        appendReading(m);
      })
      .catch(function () {
        var hero = document.querySelector('.admin-live-hero');
        var freshness = document.getElementById('admFreshness');
        var readingLabel = document.getElementById('admReadingLabel');
        if (hero) hero.classList.add('is-stale');
        if (readingLabel) readingLabel.textContent = 'Last known reading';
        if (freshness) {
          freshness.className = 'admin-freshness admin-freshness--stale';
          freshness.textContent = 'Station API unavailable · showing last received data';
        }
      });
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
