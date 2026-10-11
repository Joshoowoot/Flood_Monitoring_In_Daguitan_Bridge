(() => {
  "use strict";

  const data = window.DAGUITAN || {};

  const topbar = document.getElementById("topbar");
  const onScroll = () => topbar?.classList.toggle("is-scrolled", window.scrollY > 8);
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  const facebookFrame = document.querySelector('.fb-page-shell__embed iframe[data-responsive-width="true"]');
  if (facebookFrame) {
    let appliedWidth = 0;
    let resizeFrame = 0;
    const resizeFacebookFrame = () => {
      const availableWidth = Math.floor(facebookFrame.parentElement.clientWidth);
      const width = Math.max(180, Math.min(500, availableWidth));
      if (!availableWidth || width === appliedWidth) return;
      appliedWidth = width;
      facebookFrame.width = String(width);
      const source = new URL(facebookFrame.src);
      source.searchParams.set("width", String(width));
      facebookFrame.src = source.href;
    };
    resizeFacebookFrame();
    window.addEventListener("resize", () => {
      cancelAnimationFrame(resizeFrame);
      resizeFrame = requestAnimationFrame(resizeFacebookFrame);
    }, { passive: true });
  }

  const menu = document.getElementById("mobileMenu");
  const menuBtn = document.getElementById("menuBtn");
  const menuClose = document.getElementById("menuClose");

  const setMenu = (open) => {
    if (!menu || !menuBtn) return;
    menu.hidden = !open;
    menuBtn.setAttribute("aria-expanded", String(open));
    document.body.style.overflow = open ? "hidden" : "";
    if (open) menuClose?.focus();
  };

  menuBtn?.addEventListener("click", () => setMenu(true));
  menuClose?.addEventListener("click", () => setMenu(false));
  menu?.addEventListener("click", (event) => {
    if (event.target === menu) setMenu(false);
  });
  menu?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => setMenu(false));
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") setMenu(false);
  });

  const counters = document.querySelectorAll("[data-count]");
  const animateCount = (el) => {
    const target = Number(el.dataset.count);
    const suffix = el.dataset.suffix || "";
    if (Number.isNaN(target) || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      el.textContent = `${target.toFixed(2)}${suffix}`;
      return;
    }
    const start = performance.now();
    const tick = (now) => {
      const t = Math.min(1, (now - start) / 900);
      const eased = 1 - (1 - t) ** 3;
      el.textContent = `${(target * eased).toFixed(2)}${suffix}`;
      if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  counters.forEach(animateCount);

  let deferredPrompt = null;
  const installBtn = document.getElementById("installBtn");
  const installHint = document.getElementById("installHint");

  window.addEventListener("beforeinstallprompt", (event) => {
    event.preventDefault();
    deferredPrompt = event;
  });

  installBtn?.addEventListener("click", async () => {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      await deferredPrompt.userChoice;
      deferredPrompt = null;
      return;
    }
    if (installHint) installHint.hidden = false;
  });

  if ("serviceWorker" in navigator) {
    const manifest = document.querySelector('link[rel="manifest"]');
    const swUrl = data.serviceWorkerUrl || (manifest ? new URL("sw.js", manifest.href).href : "");
    if (swUrl) {
      navigator.serviceWorker.register(swUrl).catch((error) => {
        console.error("Daguitan offline support could not start.", error);
      });
    }
  }

  document.getElementById("notifyBtn")?.addEventListener("click", () => {
    if (isAnnouncePage || isAboutPage) {
      window.location.href = `${data.homeUrl || ""}#alerts`;
      return;
    }
    window.location.hash = "alerts";
  });

  const arrows = { rising: "↑", falling: "↓", steady: "→" };
  const fmtLevel = (n) => `${Number(n).toFixed(2)} m`;
  const fmtRate = (n) => `${n > 0 ? "+" : ""}${Number(n).toFixed(2)} cm/min`;

  document.addEventListener("click", (event) => {
    const toggle = event.target.closest("[data-toggle-announcement]");
    if (!toggle) return;
    const container = toggle.closest("#announceBody, .announce-item");
    if (!container) return;
    const expanded = container.classList.toggle("is-expanded");
    toggle.setAttribute("aria-expanded", String(expanded));
    toggle.textContent = expanded
      ? "Show less"
      : toggle.classList.contains("announce-item__toggle")
        ? "Read full announcement"
        : "Read full advisory";
  });

  document.querySelectorAll("[data-toggle-announcement]").forEach((toggle) => {
    const copy = document.getElementById(toggle.getAttribute("aria-controls"));
    if (copy) toggle.hidden = copy.textContent.trim().length <= 220;
  });

  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  const connectionText = document.getElementById("portalConnectionText");
  let connectionState = navigator.onLine ? "connected" : "offline";
  let lastMonitor = data.monitor || null;
  const renderConnection = (monitor, requestFailed = false) => {
    const connection = document.getElementById("portalConnection");
    if (!connection || !connectionText) return;
    const sensorOffline = monitor?.sensor_status === "offline" || monitor?.sensor_status === "waiting";
    connection.classList.toggle("is-offline", connectionState === "offline" || sensorOffline || requestFailed);
    if (connectionState === "offline") {
      connectionText.textContent = monitor?.last_updated
        ? `You’re offline · last sensor reading ${monitor.last_updated}`
        : "You’re offline · live flood readings are unavailable";
    } else if (requestFailed) {
      connectionText.textContent = monitor?.last_updated
        ? `Live update delayed · last sensor reading ${monitor.last_updated}`
        : "Live update delayed · waiting for a sensor reading";
    } else if (monitor?.sensor_status === "offline") {
      connectionText.textContent = `Monitoring station offline · last reading ${monitor.last_updated}`;
    } else if (monitor?.sensor_status === "waiting") {
      connectionText.textContent = "Monitoring station is waiting for its first reading";
    } else {
      connectionText.textContent = "Live station · updates every few seconds";
    }
  };

  const renderMonitor = (payload) => {
    const m = payload.monitor;
    const a = payload.announcement;
    if (!m) return;

    const arrow = arrows[m.trend] || "→";
    const ratePrefix = m.rate_cm_min > 0 ? "+" : "";
    lastMonitor = m;
    renderConnection(m);

    const badge = document.getElementById("heroStatusBadge");
    if (badge) {
      badge.className = `status-badge status-badge--${m.warning_level}`;
    }
    setText("heroStatusLabel", String(m.warning_label || "").toUpperCase());
    setText("heroStatusSr", `Warning level: ${m.warning_label}`);
    setText("heroWaterLevel", fmtLevel(m.water_level_m));
    setText("heroTrend", `${arrow} ${m.trend_label}`);
    setText("heroGuidance", (data.actions?.[m.warning_level] || data.actions?.green || [])[0] || "Follow official MDRRMO and barangay guidance.");
    const updated = document.getElementById("heroUpdated");
    if (updated) {
      if (m.last_updated_iso) updated.dateTime = m.last_updated_iso;
      else updated.removeAttribute("datetime");
      updated.textContent = m.last_updated || "No reading received";
    }

    const gauge = document.getElementById("heroGauge");
    const fill = document.getElementById("heroGaugeFill");
    if (gauge) gauge.className = `gauge gauge--${m.warning_level}`;
    if (fill) fill.style.setProperty("--level", `${m.gauge_pct || 8}%`);

    setText("cardWater", fmtLevel(m.water_level_m));
    setText("cardTrend", `${arrow} ${m.trend_label}`);
    setText("cardRate", fmtRate(m.rate_cm_min));

    setText("cardEtt", m.ett_label || "—");

    document.querySelectorAll(".warning-track__item").forEach((item) => {
      item.classList.toggle("is-active", item.dataset.level === m.warning_level);
    });

    setText("phoneStatus", String(m.warning_label || "").toUpperCase());
    setText("phoneMeta", `${Number(m.water_level_m).toFixed(2)} m · ${m.trend_label}`);
    setText("phoneWater", fmtLevel(m.water_level_m));
    setText("phoneTrend", `${arrow} ${m.trend_label}`);
    setText("phoneRate", `${ratePrefix}${Number(m.rate_cm_min).toFixed(2)}`);
    setText("phoneEtt", m.ett_label || "—");

    if (payload.weather && window.DaguitanWeather) {
      window.DaguitanWeather.apply(payload.weather);
    }

    if (a) {
      setText("announcePill", a.active ? "Active" : "Quiet");
      setText("announceScope", a.barangay ? `For Barangay ${a.barangay}` : "Municipality-wide notice");
      const body = document.getElementById("announceBody");
      if (body) {
        const expanded = body.classList.contains("is-expanded");
        const title = document.createElement(a.active ? "p" : "p");
        title.className = a.active ? `status-badge status-badge--${a.level || "yellow"}` : "announce-card__empty";
        title.textContent = a.title;
        const copy = document.createElement("p");
        copy.id = "announceCopy";
        copy.className = "live-advisory__copy";
        copy.textContent = a.body;
        const toggle = document.createElement("button");
        toggle.className = "live-advisory__toggle";
        toggle.type = "button";
        toggle.dataset.toggleAnnouncement = "";
        toggle.setAttribute("aria-controls", "announceCopy");
        toggle.setAttribute("aria-expanded", String(expanded));
        toggle.textContent = expanded ? "Show less" : "Read full advisory";
        toggle.hidden = copy.textContent.trim().length <= 220;
        body.replaceChildren(title, copy, toggle);
      }
    }

    const actionRoot = document.getElementById("portalActions");
    const actionMap = data.actions || {};
    const actionItems = actionMap[m.warning_level] || actionMap.green;
    const titles = data.guidanceTitles || ["Do this first", "Then", "Keep in mind"];
    if (actionRoot && Array.isArray(actionItems)) {
      const signature = `${m.warning_level}|${actionItems.join("|")}`;
      if (actionRoot.dataset.signature !== signature) {
        actionRoot.dataset.signature = signature;
        const next = actionItems.map((text, index) => {
          const li = document.createElement("li");
          li.className = "process__step";
          const num = document.createElement("span");
          num.className = "process__num";
          num.textContent = String(index + 1).padStart(2, "0");
          const heading = document.createElement("h3");
          heading.textContent = titles[index] || "Next";
          const copy = document.createElement("p");
          copy.textContent = text;
          li.append(num, heading, copy);
          return li;
        });
        actionRoot.replaceChildren(...next);
      }
    }
    const kicker = document.getElementById("portalGuidanceKicker");
    if (kicker && m.warning_label) {
      kicker.textContent = `${String(m.warning_label).toUpperCase()} · What to do now`;
    }
  };

  const statusUrl = data.statusUrl;
  const pollMs = Number(data.pollMs) || 5000;

  const pollStatus = async () => {
    if (!statusUrl) return;
    try {
      const res = await fetch(statusUrl, { cache: "no-store" });
      if (!res.ok) {
        renderConnection(lastMonitor, true);
        return;
      }
      const payload = await res.json();
      if (payload && payload.ok) {
        renderMonitor(payload);
        window.DaguitanMonitor.sample = payload;
      } else {
        renderConnection(lastMonitor, true);
      }
    } catch (error) {
      renderConnection(lastMonitor, true);
      console.warn("Flood monitor status could not be refreshed.", error);
    }
  };

  window.addEventListener("offline", () => {
    connectionState = "offline";
    renderConnection(lastMonitor);
  });
  window.addEventListener("online", () => {
    connectionState = "connected";
    pollStatus();
  });

  if (statusUrl) {
    setInterval(pollStatus, pollMs);
    pollStatus();
  }
  renderConnection(lastMonitor);

  window.DaguitanMonitor = {
    sample: data,
    endpoints: {
      status: statusUrl,
      ingest: data.ingestUrl || ""
    },
    refresh: pollStatus
  };
})();
