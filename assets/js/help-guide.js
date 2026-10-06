(() => {
  "use strict";

  const guide = document.querySelector(".help-action-guide");
  if (!guide) return;

  const tabs = [...guide.querySelectorAll('[role="tab"][data-level]')];
  const panels = [...guide.querySelectorAll("[data-level-panel]")];
  const currentStatus = guide.querySelector(".help-current-status");
  const currentLevel = document.getElementById("helpCurrentLevel");
  const sensorStatus = document.getElementById("helpSensorStatus");
  const lastUpdated = document.getElementById("helpLastUpdated");
  const statusMessage = document.getElementById("helpStatusMessage");
  const refreshButton = document.getElementById("helpRefreshStatus");
  const printButton = document.getElementById("helpPrint");
  const labels = { green: "Safe", yellow: "Monitor", red: "Critical" };

  const selectLevel = (level, moveFocus) => {
    tabs.forEach((tab) => {
      const selected = tab.dataset.level === level;
      tab.setAttribute("aria-selected", String(selected));
      tab.tabIndex = selected ? 0 : -1;
      if (selected && moveFocus) tab.focus();
    });
    panels.forEach((panel) => {
      panel.hidden = panel.dataset.levelPanel !== level;
    });
  };

  tabs.forEach((tab, index) => {
    tab.addEventListener("click", () => selectLevel(tab.dataset.level, false));
    tab.addEventListener("keydown", (event) => {
      let nextIndex = index;
      if (event.key === "ArrowRight") nextIndex = (index + 1) % tabs.length;
      else if (event.key === "ArrowLeft") nextIndex = (index + tabs.length - 1) % tabs.length;
      else if (event.key === "Home") nextIndex = 0;
      else if (event.key === "End") nextIndex = tabs.length - 1;
      else return;
      event.preventDefault();
      selectLevel(tabs[nextIndex].dataset.level, true);
    });
  });

  refreshButton?.addEventListener("click", async () => {
    const statusUrl = guide.dataset.statusUrl;
    if (!statusUrl) {
      statusMessage.textContent = "The monitor status address is unavailable. Open the dashboard for the latest information.";
      return;
    }

    refreshButton.disabled = true;
    statusMessage.textContent = "Refreshing monitor status…";
    try {
      const response = await fetch(statusUrl, { headers: { Accept: "application/json" }, cache: "no-store" });
      if (!response.ok) throw new Error(`Monitor status request failed (${response.status}).`);
      const payload = await response.json();
      const monitor = payload && payload.monitor;
      const level = monitor && monitor.warning_level;
      if (!payload.ok || !labels[level]) throw new Error("The monitor returned an invalid status.");

      currentStatus.dataset.level = level;
      currentLevel.textContent = labels[level];
      sensorStatus.textContent = `Sensor: ${monitor.sensor_label || "Status unavailable"}`;
      lastUpdated.textContent = monitor.last_updated || "Not available";
      statusMessage.textContent = "Monitor status refreshed.";
      selectLevel(level, false);
      guide.dataset.currentLevel = level;
    } catch (error) {
      statusMessage.textContent = "Could not refresh monitor status. Check the dashboard and official announcements for updates.";
    } finally {
      refreshButton.disabled = false;
    }
  });

  printButton?.addEventListener("click", () => window.print());
})();
