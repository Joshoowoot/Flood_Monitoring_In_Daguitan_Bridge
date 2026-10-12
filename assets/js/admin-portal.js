(function () {
  var cfg = window.DAGUITAN_ADMIN || {};
  var canvas = document.getElementById('adminWaterChart');
  var historyCanvas = document.getElementById('adminHistoryChart');

  function drawChart() {
    if (!canvas || !cfg.chart || !cfg.chart.points) return;
    var windowSeconds = Number(canvas.getAttribute('data-window-seconds')) || 3600;
    var now = Math.floor(Date.now() / 1000);
    var start = now - windowSeconds;
    var points = cfg.chart.points.filter(function (point) {
      return Number(point.ts) >= start && Number(point.ts) <= now && Number.isFinite(Number(point.water_level_m));
    });
    var empty = document.getElementById('admChartEmpty');
    if (!empty) empty = document.getElementById('adminAnalyticsChartEmpty');
    canvas.hidden = points.length === 0;
    if (empty) empty.hidden = points.length > 0;
    if (!points.length) return;
    var pointStart = Math.min.apply(null, points.map(function (point) { return Number(point.ts); }));
    if (canvas.getAttribute('data-window-labels') === 'analytics') {
      start = Math.min(start, pointStart);
      windowSeconds = Math.max(3600, now - start);
    }
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
    var yellow = parseFloat(cfg.chart.yellow || cfg.thresholds.yellow || 0.1);
    var red = parseFloat(cfg.chart.red || cfg.thresholds.red || 0.2);
    var maxValue = Math.max.apply(null, points.map(function (point) { return Number(point.water_level_m); }));
    var maxY = Math.max(red * 1.15, maxValue * 1.12, 0.3);
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
    ctx.font = '700 10px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
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
    ctx.font = '11px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    ctx.fillText('0 m', 6, yScale(0) + 4);
    ctx.fillText(yellow.toFixed(2) + ' m', 6, yScale(yellow) + 4);
    ctx.fillText(red.toFixed(2) + ' m', 6, yScale(red) + 4);
    var analyticsRange = canvas.getAttribute('data-window-labels') === 'analytics';
    if (analyticsRange) {
      var span = now - start;
      var dateFormat = span <= 2 * 86400 ? { day: 'numeric', month: 'short', hour: 'numeric' }
        : (span <= 120 * 86400 ? { day: 'numeric', month: 'short' } : { month: 'short', year: 'numeric' });
      function formatDate(ts) {
        return new Date(ts * 1000).toLocaleDateString(undefined, dateFormat);
      }
      ctx.fillText(formatDate(start), pad.l, h - 7);
      ctx.textAlign = 'center';
      ctx.fillText(formatDate(start + span / 2), pad.l + innerW / 2, h - 7);
      ctx.textAlign = 'right';
      ctx.fillText(formatDate(now), w - pad.r, h - 7);
      ctx.textAlign = 'left';
    } else {
      ctx.fillText('60 min ago', pad.l, h - 7);
    }
    if (!analyticsRange) {
      ctx.textAlign = 'center';
      ctx.fillText('30 min ago', pad.l + innerW / 2, h - 7);
      ctx.textAlign = 'right';
      ctx.fillText('Now', w - pad.r, h - 7);
      ctx.textAlign = 'left';
    }

    ctx.beginPath();
    ctx.arc(lastX, lastY, 4, 0, Math.PI * 2);
    ctx.fillStyle = '#9b1224';
    ctx.fill();
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 2;
    ctx.stroke();
    if (points.length === 1) {
      ctx.beginPath();
      ctx.arc(lastX, lastY, 4, 0, Math.PI * 2);
      ctx.fillStyle = '#9b1224';
      ctx.fill();
      ctx.strokeStyle = '#fff';
      ctx.lineWidth = 2;
      ctx.stroke();
    }
    ctx.fillStyle = '#1a1012';
    ctx.font = '700 12px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    ctx.fillText(Number(points[points.length - 1].water_level_m).toFixed(2) + ' m', Math.max(pad.l, lastX - 20), Math.max(14, lastY - 10));
  }

  function drawHistoryChart(periodKey) {
    if (!historyCanvas || !cfg.historyChart || !cfg.historyChart.periods) return;
    var period = cfg.historyChart.periods[periodKey];
    if (!period) return;
    var points = period.points || [];
    var empty = document.getElementById('adminHistoryChartEmpty');
    historyCanvas.hidden = points.length === 0;
    if (empty) empty.hidden = points.length > 0;
    if (!points.length) return;

    var ctx = historyCanvas.getContext('2d');
    var dpr = window.devicePixelRatio || 1;
    var rect = historyCanvas.getBoundingClientRect();
    if (!rect.width || !rect.height) return;
    historyCanvas.width = Math.round(rect.width * dpr);
    historyCanvas.height = Math.round(rect.height * dpr);
    ctx.scale(dpr, dpr);

    var w = rect.width;
    var h = rect.height;
    var pad = { t: 18, r: 20, b: 38, l: 52 };
    var yellow = Number(cfg.historyChart.yellow) || 0.1;
    var red = Number(cfg.historyChart.red) || 0.2;
    var start = Number(period.start);
    var end = Number(period.end);
    var values = [];
    points.forEach(function (point) {
      ['green', 'yellow', 'red'].forEach(function (key) {
        if (point[key] != null && Number.isFinite(Number(point[key]))) values.push(Number(point[key]));
      });
    });
    var maxValue = values.length ? Math.max.apply(null, values) : red;
    var maxY = Math.max(red * 1.12, Math.ceil(maxValue * 1.12 * 2) / 2, 0.5);
    var innerW = w - pad.l - pad.r;
    var innerH = h - pad.t - pad.b;
    function xScale(ts) {
      return pad.l + ((ts - start) / Math.max(1, end - start)) * innerW;
    }
    function yScale(value) {
      return pad.t + innerH - (value / maxY) * innerH;
    }

    ctx.clearRect(0, 0, w, h);
    ctx.font = '11px system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    ctx.lineWidth = 1;
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';
    for (var tick = 0; tick <= 4; tick += 1) {
      var value = (maxY / 4) * tick;
      var y = yScale(value);
      ctx.strokeStyle = 'rgba(39, 53, 69, 0.1)';
      ctx.beginPath();
      ctx.moveTo(pad.l, y);
      ctx.lineTo(w - pad.r, y);
      ctx.stroke();
      ctx.fillStyle = '#687585';
      ctx.fillText(value.toFixed(1) + ' m', pad.l - 9, y);
    }

    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    var labelCount = Math.min(6, points.length);
    var seenLabels = {};
    for (var labelIndex = 0; labelIndex < labelCount; labelIndex += 1) {
      var pointIndex = labelCount === 1 ? 0 : Math.round(labelIndex * (points.length - 1) / (labelCount - 1));
      var point = points[pointIndex];
      if (!point || !point.label || seenLabels[point.label]) continue;
      seenLabels[point.label] = true;
      ctx.fillStyle = '#687585';
      ctx.fillText(point.label, xScale(Number(point.ts)), h - pad.b + 12);
    }

    [
      { key: 'green', color: '#16834a' },
      { key: 'yellow', color: '#c48a00' },
      { key: 'red', color: '#c3323c' }
    ].forEach(function (line) {
      ctx.strokeStyle = line.color;
      ctx.fillStyle = line.color;
      ctx.lineWidth = 2.5;
      ctx.lineJoin = 'round';
      ctx.lineCap = 'round';
      var active = false;
      ctx.beginPath();
      points.forEach(function (point) {
        var value = point[line.key];
        if (value == null || !Number.isFinite(Number(value))) {
          active = false;
          return;
        }
        var x = xScale(Number(point.ts));
        var y = yScale(Number(value));
        if (active) ctx.lineTo(x, y);
        else ctx.moveTo(x, y);
        active = true;
      });
      ctx.stroke();

      points.forEach(function (point) {
        var value = point[line.key];
        if (value == null || !Number.isFinite(Number(value))) return;
        ctx.beginPath();
        ctx.arc(xScale(Number(point.ts)), yScale(Number(value)), points.length > 80 ? 1.8 : 3, 0, Math.PI * 2);
        ctx.fill();
      });
    });
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
    if (label) label.textContent = isLive ? 'River level' : (isWaiting ? 'Waiting for a reading' : 'Last known river level');
    if (fresh) {
      fresh.className = 'admin-freshness admin-freshness--' + (isLive ? 'live' : (isWaiting ? 'waiting' : 'stale'));
      fresh.textContent = isLive ? 'Live telemetry · packet received ' + ageLabel : (isWaiting ? 'Waiting for first telemetry' : 'Station offline · last packet ' + ageLabel);
    }
  }

  function updateThreshold(m) {
    var level = Number(m.water_level_m);
    var yellow = Number(m.threshold_yellow_m || cfg.thresholds.yellow || 0.1);
    var red = Number(m.threshold_red_m || cfg.thresholds.red || 0.2);
    var context = m.sensor_status === 'waiting' ? 'Waiting for telemetry' : (level >= red ? 'Critical threshold reached' : (level >= yellow ? (red - level).toFixed(2) + ' m to critical threshold' : (yellow - level).toFixed(2) + ' m to monitor threshold'));
    var distance = '—';
    var distanceTarget = 'to advisory threshold';
    if (m.sensor_status === 'waiting') {
      distanceTarget = 'Waiting for telemetry';
    } else if (level >= red) {
      distanceTarget = 'Critical threshold reached';
    } else if (level >= yellow) {
      distance = Math.max(0, red - level).toFixed(2) + ' m';
      distanceTarget = 'to critical threshold';
    } else {
      distance = Math.max(0, yellow - level).toFixed(2) + ' m';
    }
    var meter = document.getElementById('admThresholdMeter');
    var fill = document.getElementById('admThresholdFill');
    var warning = m.warning_level || 'green';
    setText('admThresholdContext', context);
    setText('admThresholdDistance', distance);
    setText('admThresholdTarget', distanceTarget);
    if (meter) {
      meter.setAttribute('aria-valuenow', level.toFixed(2));
      meter.setAttribute('aria-valuetext', m.sensor_status === 'waiting' ? 'Waiting for telemetry' : level.toFixed(2) + ' m');
    }
    if (fill) {
      fill.style.width = m.sensor_status === 'waiting' ? '0%' : Math.max(0, Math.min(100, level / Math.max(red, 0.01) * 100)) + '%';
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
      var isWaiting = m.sensor_status === 'waiting';
      el.textContent = isWaiting ? 'Awaiting telemetry' : m.warning_label;
      el.className = 'status-badge status-badge--' + (isWaiting ? 'waiting' : m.warning_level);
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
          setText('admWaitingCopy', String(data.sync.pending_total) + ' row(s)');
        }
        if (data.sync) {
          if (data.sync.cloud_target) setText('admCloudTarget', data.sync.cloud_target);
          if (data.sync.cloud_database) setText('admCloudDatabase', data.sync.cloud_database);
          setText('admLastCloudCopy', data.sync.last_synced_at || 'Not yet');
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
          if (key === 'esp32' || key === 'ultrasonic' || key === 'power') {
            var sensorState = m.sensor_status === 'online' ? 'online' : 'offline';
            status.textContent = sensorState === 'online' ? 'Online' : 'Offline';
            status.className = 'admin-sensor-status admin-sensor-status--' + sensorState;
            card.classList.remove('admin-sensor-card--online', 'admin-sensor-card--offline', 'admin-sensor-card--warning');
            card.classList.add('admin-sensor-card--' + sensorState);
            detail.textContent = sensorState === 'online' ? 'Telemetry connected; source not reported' : 'No recent telemetry available';
          } else if (key === 'solar' || key === 'battery') {
            status.textContent = 'Telemetry unavailable';
            status.className = 'admin-sensor-status admin-sensor-status--warning';
            card.classList.remove('admin-sensor-card--online', 'admin-sensor-card--offline', 'admin-sensor-card--warning');
            card.classList.add('admin-sensor-card--warning');
            detail.textContent = 'Waiting for ESP power telemetry';
          }
        });
        var sensorCards = document.querySelectorAll('[data-infra-key]');
        var onlineSensors = document.querySelectorAll('[data-infra-key] .admin-sensor-status--online').length;
        setText('admSensorOnlineCount', onlineSensors);
        setText('admSensorAttentionCount', Math.max(0, sensorCards.length - onlineSensors));
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
  var historyPeriodButtons = document.querySelectorAll('[data-history-period]');
  function selectedHistoryPeriod() {
    var selected = document.querySelector('[data-history-period][aria-pressed="true"]');
    return selected ? selected.getAttribute('data-history-period') : 'week';
  }
  historyPeriodButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      historyPeriodButtons.forEach(function (periodButton) {
        var isSelected = periodButton === button;
        periodButton.classList.toggle('is-active', isSelected);
        periodButton.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
      });
      drawHistoryChart(button.getAttribute('data-history-period'));
      var period = cfg.historyChart && cfg.historyChart.periods[button.getAttribute('data-history-period')];
      if (historyCanvas && period) {
        historyCanvas.setAttribute('aria-label', 'Average water level by warning category for ' + period.label.toLowerCase());
      }
    });
  });
  drawHistoryChart(selectedHistoryPeriod());
  window.addEventListener('resize', drawChart);
  window.addEventListener('resize', function () { drawHistoryChart(selectedHistoryPeriod()); });
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

  document.querySelectorAll('.admin-residents-page .admin-residents-sort, .admin-reports-page .admin-reports-sort').forEach(function (control) {
    var table = control.closest('.dash-table-wrap').querySelector('.dash-table');
    var body = table && table.tBodies[0];
    if (!body) return;
    var originalRows = Array.prototype.slice.call(body.rows);
    var visibleLimit = Number(table.getAttribute('data-sort-visible-limit')) || 0;
    var showMore = control.closest('.dash-table-wrap').querySelector('[data-sort-toggle]');
    var expanded = false;

    function updateVisibleRows(rows) {
      rows.forEach(function (row, index) {
        row.hidden = visibleLimit > 0 && !expanded && index >= visibleLimit;
      });
      if (showMore) {
        showMore.hidden = visibleLimit <= 0 || rows.length <= visibleLimit;
        showMore.textContent = expanded ? 'Show fewer' : 'Show all ' + rows.length + ' alerts';
        showMore.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      }
    }

    updateVisibleRows(originalRows);

    if (showMore) {
      showMore.addEventListener('click', function () {
        expanded = !expanded;
        updateVisibleRows(Array.prototype.slice.call(body.rows));
      });
    }

    control.addEventListener('change', function () {
      if (!control.value) {
        originalRows.forEach(function (row) { body.appendChild(row); });
        updateVisibleRows(originalRows);
        return;
      }
      var settings = control.value.split(':');
      var column = Number(settings[0]);
      var direction = settings[1] === 'asc' ? 1 : -1;
      var type = settings[2];
      var rows = Array.prototype.slice.call(body.rows);

      rows.sort(function (rowA, rowB) {
        var cellA = rowA.cells[column];
        var cellB = rowB.cells[column];
        var valueA = cellA ? (cellA.getAttribute('data-sort-value') || cellA.textContent.trim()) : '';
        var valueB = cellB ? (cellB.getAttribute('data-sort-value') || cellB.textContent.trim()) : '';
        var emptyA = valueA === '' || valueA === '—';
        var emptyB = valueB === '' || valueB === '—';

        if (emptyA || emptyB) return emptyA === emptyB ? 0 : (emptyA ? 1 : -1);

        if (type === 'number') {
          valueA = Number(valueA);
          valueB = Number(valueB);
        } else {
          valueA = /^\d+$/.test(valueA) ? Number(valueA) : Date.parse(valueA.replace(' ', 'T'));
          valueB = /^\d+$/.test(valueB) ? Number(valueB) : Date.parse(valueB.replace(' ', 'T'));
        }

        if (valueA === valueB) return 0;
        return (valueA < valueB ? -1 : 1) * direction;
      });

      rows.forEach(function (row) { body.appendChild(row); });
      updateVisibleRows(rows);
    });
  });

  window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 961px)').matches) {
      setNavOpen(false);
    }
  });
})();
