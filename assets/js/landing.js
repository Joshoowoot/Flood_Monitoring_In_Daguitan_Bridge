(() => {
  "use strict";

  const data = window.DAGUITAN || {};

  const topbar = document.getElementById("topbar");
  const onScroll = () => topbar?.classList.toggle("is-scrolled", window.scrollY > 8);
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

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

  const navLinks = document.querySelectorAll(".bottom-nav a");
  const isAnnouncePage = document.body.classList.contains("announce-page");
  const sections = ["home", "monitor", "guidance", "alerts", "safety", "about"]
    .map((id) => document.getElementById(id))
    .filter(Boolean);

  const syncNav = () => {
    if (isAnnouncePage) return;
    const y = window.scrollY + 120;
    let current = "home";
    sections.forEach((section) => {
      if (section.offsetTop <= y) current = section.id;
    });
    navLinks.forEach((link) => {
      const href = link.getAttribute("href") || "";
      const hash = href.includes("#") ? href.slice(href.indexOf("#")) : "";
      const isHomeLink = !href.includes("#");
      const active = current === "home" ? isHomeLink : hash === `#${current}`;
      link.classList.toggle("is-active", active);
    });
  };
  window.addEventListener("scroll", syncNav, { passive: true });
  syncNav();

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
    const swUrl = new URL("sw.js", document.baseURI || window.location.href);
    navigator.serviceWorker.register(swUrl.href).catch(() => {
      /* Landing remains usable if the worker cannot register. */
    });
  }

  document.getElementById("notifyBtn")?.addEventListener("click", () => {
    if (isAnnouncePage) {
      window.location.href = `${data.homeUrl || ""}#alerts`;
      return;
    }
    window.location.hash = "alerts";
  });

  const arrows = { rising: "↑", falling: "↓", steady: "→" };
  const fmtLevel = (n) => `${Number(n).toFixed(2)} m`;
  const fmtRate = (n) => `${n > 0 ? "+" : ""}${Number(n).toFixed(2)} cm/min`;

  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  const renderMonitor = (payload) => {
    const m = payload.monitor;
    const a = payload.announcement;
    if (!m) return;

    const arrow = arrows[m.trend] || "→";
    const ratePrefix = m.rate_cm_min > 0 ? "+" : "";

    const badge = document.getElementById("heroStatusBadge");
    if (badge) {
      badge.className = `status-badge status-badge--${m.warning_level}`;
    }
    setText("heroStatusLabel", String(m.warning_label || "").toUpperCase());
    setText("heroStatusSr", `Warning level: ${m.warning_label}`);
    setText("heroWaterLevel", fmtLevel(m.water_level_m));
    setText("heroTrend", `${arrow} ${m.trend_label}`);
    const updated = document.getElementById("heroUpdated");
    if (updated) {
      updated.dateTime = m.last_updated_iso || "";
      updated.textContent = m.last_updated;
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
      const body = document.getElementById("announceBody");
      if (body) {
        const title = document.createElement(a.active ? "p" : "p");
        title.className = a.active ? `status-badge status-badge--${a.level || "yellow"}` : "announce-card__empty";
        title.textContent = a.title;
        const copy = document.createElement("p");
        copy.textContent = a.body;
        body.replaceChildren(title, copy);
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
      if (!res.ok) return;
      const payload = await res.json();
      if (payload && payload.ok) {
        renderMonitor(payload);
        window.DaguitanMonitor.sample = payload;
      }
    } catch (err) {
      /* Keep last known values if the station is unreachable. */
    }
  };

  if (statusUrl) {
    setInterval(pollStatus, pollMs);
    pollStatus();
  }

  window.DaguitanMonitor = {
    sample: data,
    endpoints: {
      status: statusUrl,
      ingest: data.ingestUrl || ""
    },
    refresh: pollStatus
  };
})();
