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

  const warningDetails = document.getElementById("warningDetails");
  const warningDetailsMeter = document.getElementById("warningDetailsMeter");
  let warningDetailsTrigger = null;
  const communityImpacts = {
    green: {
      intro: "The water is below the advisory threshold. Flood impacts are less likely at this level, but heavy rain or changing river conditions can raise the risk.",
      effects: [
        "No immediate flood impact is indicated by this reading."
      ],
      actions: [
        "Continue normal activities and keep an eye on official updates.",
        "Know your household’s evacuation route and keep drains clear."
      ]
    },
    yellow: {
      intro: "The river has reached the caution range. Risk may increase in low-lying places, especially if the water keeps rising or heavy rain continues.",
      effects: [
        "Water may collect near riverbanks and on low-lying roads.",
        "Travel may become slippery or difficult in affected areas."
      ],
      actions: [
        "Prepare essential items and follow official updates.",
        "Check on children, older adults, and neighbors who may need help.",
        "Stay away from the river and never cross floodwater."
      ]
    },
    red: {
      intro: "The critical threshold has been reached. Flooding may affect low-lying homes, roads, and nearby areas. Fast-moving water and debris can be dangerous.",
      effects: [
        "Flooding may reach homes and roads in low-lying areas.",
        "Fast or deep water can make travel life-threatening."
      ],
      actions: [
        "Move to higher ground if water is rising near you.",
        "Follow MDRRMO or barangay evacuation instructions promptly.",
        "Never walk or drive through floodwater. Contact local emergency services if someone is in immediate danger."
      ]
    }
  };
  const formatMeters = (value) => `${Number(value).toFixed(2)} m`;
  const updateCommunityImpacts = (level) => {
    const impacts = communityImpacts[level] || communityImpacts.green;
    setText("warningDetailsCommunityIntro", impacts.intro);
    const fillList = (id, entries) => {
      const list = document.getElementById(id);
      if (!list) return;
      const items = entries.map((text) => {
        const item = document.createElement("li");
        item.textContent = text;
        return item;
      });
      list.replaceChildren(...items);
    };
    fillList("warningDetailsEffects", impacts.effects);
    fillList("warningDetailsActions", impacts.actions);
  };
  const updateWarningDetails = (monitor) => {
    const yellow = Number(monitor?.threshold_yellow_m ?? data.thresholds?.yellow ?? 0.1);
    const red = Number(monitor?.threshold_red_m ?? data.thresholds?.red ?? 0.2);
    const level = Number(monitor?.water_level_m ?? 0);
    const max = Math.max(red * 1.2, red + 0.5);
    const yellowStop = `${Math.min(100, Math.max(0, (yellow / max) * 100))}%`;
    const redStop = `${Math.min(100, Math.max(0, (red / max) * 100))}%`;
    const readingPosition = `${Math.min(100, Math.max(0, (level / max) * 100))}%`;

    setText("warningDetailsCurrent", formatMeters(level));
    setText("warningDetailsYellow", formatMeters(yellow));
    setText("warningDetailsRed", formatMeters(red));
    warningDetailsMeter?.setAttribute("aria-valuenow", String(level));
    warningDetailsMeter?.setAttribute("aria-valuemax", String(max));
    warningDetailsMeter?.setAttribute("aria-valuetext", `Current water level ${formatMeters(level)}; advisory threshold ${formatMeters(yellow)}; critical threshold ${formatMeters(red)}`);
    warningDetailsMeter?.style.setProperty("--yellow-stop", yellowStop);
    warningDetailsMeter?.style.setProperty("--red-stop", redStop);
    warningDetailsMeter?.style.setProperty("--reading-position", readingPosition);
    if (warningDetailsTrigger) {
      const selectedLevel = warningDetailsTrigger.dataset.warningDetails;
      const range = selectedLevel === "green"
        ? `Below ${formatMeters(yellow)} (advisory threshold).`
        : selectedLevel === "yellow"
          ? `From ${formatMeters(yellow)} to below ${formatMeters(red)}.`
          : `At or above ${formatMeters(red)}.`;
      setText("warningDetailsRange", range);
    }
  };
  const closeWarningDetails = () => {
    if (!warningDetails?.open) return;
    warningDetails.close();
  };
  const resetWarningDetailsTrigger = () => {
    warningDetailsTrigger?.setAttribute("aria-expanded", "false");
    warningDetailsTrigger = null;
  };

  if (warningDetails) {
    document.body.append(warningDetails);
    document.addEventListener("click", (event) => {
      const button = event.target.closest("[data-warning-details]");
      if (!button) return;
      if (warningDetailsTrigger === button && warningDetails.open) {
        closeWarningDetails();
        return;
      }
      warningDetailsTrigger?.setAttribute("aria-expanded", "false");
      warningDetailsTrigger = button;
      warningDetailsTrigger.setAttribute("aria-expanded", "true");
      warningDetails.dataset.level = button.dataset.warningDetails;
      setText("warningDetailsLevel", `${button.dataset.warningDetails.toUpperCase()} warning level`);
      const selectedTitle = button.querySelector(".warn-board__spec strong")?.textContent || button.querySelector("#heroStatusLabel")?.textContent || "Water-level details";
      setText("warningDetailsTitle", selectedTitle.trim().toLowerCase().replace(/\b\w/g, (character) => character.toUpperCase()));
      updateCommunityImpacts(button.dataset.warningDetails);
      updateWarningDetails(lastMonitor);
      if (!warningDetails.open) warningDetails.showModal();
    });
    warningDetails.querySelector(".warning-details__close")?.addEventListener("click", closeWarningDetails);
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && warningDetails.open) {
        event.preventDefault();
        closeWarningDetails();
      }
    });
    warningDetails.addEventListener("click", (event) => {
      if (event.target === warningDetails) {
        closeWarningDetails();
      }
    });
    warningDetails.addEventListener("close", resetWarningDetailsTrigger);
  }

  const connectionText = document.getElementById("portalConnectionText");
  let connectionState = navigator.onLine ? "connected" : "offline";
  let lastMonitor = data.monitor || null;
  const renderConnection = (monitor, requestFailed = false) => {
    const connection = document.getElementById("portalConnection");
    if (!connection || !connectionText) return;
    const sensorOffline = monitor?.sensor_status === "offline" || monitor?.sensor_status === "waiting";
    connection.classList.toggle("is-offline", connectionState === "offline" || sensorOffline || requestFailed);
    if (connectionState === "offline") {
      connectionText.textContent = "You’re offline · showing the last saved reading";
    } else if (requestFailed) {
      connectionText.textContent = "Live update delayed · showing the last saved reading";
    } else if (monitor?.sensor_status === "offline") {
      connectionText.textContent = "Monitoring station offline · showing the last saved reading";
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
    updateWarningDetails(m);

    const badge = document.getElementById("heroStatusBadge");
    if (badge) {
      badge.className = `status-badge status-badge--${m.warning_level}`;
      badge.dataset.warningDetails = m.warning_level;
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
