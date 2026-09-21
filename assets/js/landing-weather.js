(() => {
  "use strict";

  const scene = document.getElementById("wxScene");
  const rainBack = document.getElementById("wxRainBack");
  const rainFront = document.getElementById("wxRainFront");
  const lightning = document.getElementById("wxLightning");
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const RAIN_THEMES = new Set(["drizzle", "rainy", "storm"]);
  let currentTheme = scene?.dataset.theme || "cloudy";
  let rainBuilt = false;
  let lightningTimer = null;

  function rainIntensity(theme) {
    if (theme === "drizzle") return { back: 35, front: 45, speed: [0.75, 1.1] };
    if (theme === "rainy") return { back: 55, front: 90, speed: [0.45, 0.85] };
    if (theme === "storm") return { back: 80, front: 120, speed: [0.35, 0.65] };
    return { back: 0, front: 0, speed: [1, 1] };
  }

  function buildRain(theme) {
    if (!rainBack || !rainFront || reducedMotion) return;
    rainBack.replaceChildren();
    rainFront.replaceChildren();
    if (!RAIN_THEMES.has(theme)) {
      rainBuilt = true;
      return;
    }

    const cfg = rainIntensity(theme);
    const mk = (layer, count, heightRange) => {
      for (let i = 0; i < count; i += 1) {
        const drop = document.createElement("span");
        drop.style.left = `${Math.random() * 100}%`;
        drop.style.height = `${heightRange[0] + Math.random() * (heightRange[1] - heightRange[0])}px`;
        const dur = cfg.speed[0] + Math.random() * (cfg.speed[1] - cfg.speed[0]);
        drop.style.animationDuration = `${dur}s`;
        drop.style.animationDelay = `${Math.random() * dur}s`;
        layer.appendChild(drop);
      }
    };

    mk(rainBack, cfg.back, [18, 28]);
    mk(rainFront, cfg.front, [26, 40]);
    rainBuilt = true;
  }

  function scheduleLightning(theme) {
    if (lightningTimer) {
      clearTimeout(lightningTimer);
      lightningTimer = null;
    }
    if (!lightning || theme !== "storm" || reducedMotion) return;

    const flash = () => {
      lightning.classList.remove("is-flash");
      void lightning.offsetWidth;
      lightning.classList.add("is-flash");
      lightningTimer = setTimeout(flash, 6000 + Math.random() * 9000);
    };
    lightningTimer = setTimeout(flash, 4000 + Math.random() * 5000);
  }

  function applyTheme(theme) {
    if (!scene || !theme) return;
    const changed = theme !== currentTheme;
    currentTheme = theme;
    scene.dataset.theme = theme;
    if (changed || !rainBuilt) buildRain(theme);
    scheduleLightning(theme);
  }

  function updateCard(weather) {
    if (!weather) return;
    const theme = weather.theme || "cloudy";
    const set = (id, text) => {
      const el = document.getElementById(id);
      if (el) el.textContent = text;
    };
    set("wxCardTemp", `${Number(weather.temp_c)}°C`);
    set("wxCardCond", weather.condition || "—");
    set("wxCardHumidity", `${Number(weather.humidity)}%`);
    set("wxCardRain", `${Number(weather.rainfall_mm).toFixed(1)} mm`);
    set("wxCardWind", `${Number(weather.wind_kmh)} km/h`);
    set("wxCardSource", weather.source || "");
    const icon = document.getElementById("wxCardIcon");
    if (icon) icon.dataset.theme = theme;
  }

  function applyWeather(weather) {
    if (!weather) return;
    const theme = weather.theme || "cloudy";
    applyTheme(theme);
    updateCard(weather);
  }

  window.DaguitanWeather = {
    apply: applyWeather,
    theme: () => currentTheme,
  };

  const initial = window.DAGUITAN?.weather;
  if (initial) applyWeather(initial);
  else if (scene) buildRain(currentTheme);
})();
