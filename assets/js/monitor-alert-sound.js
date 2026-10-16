(function () {
  "use strict";

  const AudioContextConstructor = window.AudioContext || window.webkitAudioContext;
  const panel = document.createElement("aside");
  const header = document.createElement("div");
  const headingCopy = document.createElement("div");
  const icon = document.createElement("span");
  const severity = document.createElement("span");
  const title = document.createElement("strong");
  const message = document.createElement("span");
  const status = document.createElement("span");
  const playButton = document.createElement("button");
  const stopButton = document.createElement("button");
  const actions = document.createElement("div");
  let context = null;
  let level = null;
  let running = false;
  let playing = false;
  let timer = 0;
  let oscillators = [];
  let session = 0;

  panel.className = "monitor-audio-alert";
  panel.hidden = true;
  panel.setAttribute("role", "region");
  panel.setAttribute("aria-label", "Flood warning sound controls");
  header.className = "monitor-audio-alert__header";
  headingCopy.className = "monitor-audio-alert__heading-copy";
  icon.className = "monitor-audio-alert__icon";
  icon.setAttribute("aria-hidden", "true");
  icon.textContent = "!";
  severity.className = "monitor-audio-alert__severity";
  title.className = "monitor-audio-alert__title";
  title.setAttribute("role", "alert");
  title.setAttribute("aria-live", "assertive");
  message.className = "monitor-audio-alert__message";
  message.id = "monitorAudioAlertMessage";
  status.className = "monitor-audio-alert__status";
  status.setAttribute("role", "status");
  status.setAttribute("aria-live", "polite");
  playButton.className = "monitor-audio-alert__play";
  playButton.type = "button";
  playButton.textContent = "Play sound";
  playButton.setAttribute("aria-describedby", message.id);
  stopButton.className = "monitor-audio-alert__stop";
  stopButton.type = "button";
  stopButton.textContent = "Stop alarm";
  actions.className = "monitor-audio-alert__actions";
  actions.append(playButton, stopButton);
  headingCopy.append(severity, title);
  header.append(icon, headingCopy);
  panel.append(header, message, status, actions);
  document.body.append(panel);

  const updateControls = function () {
    playButton.disabled = !AudioContextConstructor || !level || running;
    playButton.textContent = running ? "Alarm active" : "Play alarm";
    stopButton.disabled = !running;
    panel.dataset.audioState = running ? (playing ? "playing" : "waiting") : "stopped";
  };

  const stop = function () {
    session++;
    window.clearTimeout(timer);
    timer = 0;
    running = false;
    playing = false;
    oscillators.forEach(function (oscillator) {
      try {
        oscillator.stop();
      } catch (error) {
        console.warn("Flood alert sound could not be stopped.", error);
      }
    });
    oscillators = [];
    updateControls();
  };

  const playCycle = function () {
    if (!context || !running || !level || playing) return;
    if (context.state !== "running") {
      running = false;
      playing = false;
      status.textContent = "Audio was paused by the browser. Select Play sound to resume.";
      updateControls();
      return;
    }

    playing = true;
    status.textContent = "Alarm is sounding. Select Stop alarm to silence it.";
    updateControls();
    const activeLevel = level;
    const duration = activeLevel === "red" ? 10 : 8;
    const startAt = context.currentTime + 0.05;
    const activeSession = session;
    if (activeLevel === "red") {
      const lowFrequency = 560;
      const highFrequency = 1080;
      const wailDuration = 2.05;
      const wailPeriod = 2.45;

      [1, 2].forEach(function (harmonic, index) {
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const volume = index === 0 ? 0.17 : 0.035;
        oscillator.type = index === 0 ? "sine" : "triangle";
        oscillator.frequency.setValueAtTime(lowFrequency * harmonic, startAt);

        for (let wailAt = startAt; wailAt < startAt + duration; wailAt += wailPeriod) {
          const wailEnd = Math.min(wailAt + wailDuration, startAt + duration);
          const riseEnd = Math.min(wailAt + wailDuration * 0.48, wailEnd);
          const fadeStart = Math.max(riseEnd, wailEnd - 0.1);
          gain.gain.setValueAtTime(0.0001, wailAt);
          gain.gain.linearRampToValueAtTime(volume, wailAt + 0.12);
          gain.gain.setValueAtTime(volume, fadeStart);
          gain.gain.linearRampToValueAtTime(0.0001, wailEnd);

          oscillator.frequency.setValueAtTime(lowFrequency * harmonic, wailAt);
          oscillator.frequency.linearRampToValueAtTime(highFrequency * harmonic, riseEnd);
          oscillator.frequency.linearRampToValueAtTime(lowFrequency * harmonic, wailEnd);
        }

        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.onended = function () {
          oscillators = oscillators.filter(function (active) { return active !== oscillator; });
        };
        oscillators.push(oscillator);
        oscillator.start(startAt);
        oscillator.stop(startAt + duration + 0.02);
      });
    } else {
      const lowFrequency = 520;
      const highFrequency = 820;
      const period = 2.1;
      const wailDuration = period - 0.2;

      [1, 2].forEach(function (harmonic, index) {
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const layerVolume = 0.12 / (index === 0 ? 1 : 7);
        oscillator.type = "sine";
        oscillator.frequency.setValueAtTime(lowFrequency * harmonic, startAt);

        for (let wailAt = startAt; wailAt < startAt + duration; wailAt += period) {
          const wailEnd = Math.min(wailAt + wailDuration, startAt + duration);
          const peakAt = Math.min(wailAt + 0.08, wailEnd);
          const fadeAt = Math.max(peakAt, wailEnd - 0.08);
          gain.gain.setValueAtTime(0.0001, wailAt);
          gain.gain.linearRampToValueAtTime(layerVolume, peakAt);
          gain.gain.setValueAtTime(layerVolume, fadeAt);
          gain.gain.linearRampToValueAtTime(0.0001, wailEnd);

          oscillator.frequency.setValueAtTime(lowFrequency * harmonic, wailAt);
          oscillator.frequency.linearRampToValueAtTime(highFrequency * harmonic, wailAt + wailDuration / 2);
          oscillator.frequency.linearRampToValueAtTime(lowFrequency * harmonic, wailEnd);
        }

        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.onended = function () {
          oscillators = oscillators.filter(function (active) { return active !== oscillator; });
        };
        oscillators.push(oscillator);
        oscillator.start(startAt);
        oscillator.stop(startAt + duration + 0.02);
      });
    }

    timer = window.setTimeout(function () {
      if (activeSession !== session || !running) return;
      playing = false;
      oscillators = [];
      status.textContent = "Brief pause · the alarm will repeat.";
      updateControls();
      timer = window.setTimeout(function () {
        if (activeSession === session && running) playCycle();
      }, 1000);
    }, duration * 1000);
  };

  const start = function () {
    if (!AudioContextConstructor || !level || running) return;
    const activeSession = ++session;
    const activeLevel = level;
    status.textContent = "Starting sound…";
    updateControls();
    try {
      if (!context) context = new AudioContextConstructor();
      if (context.state === "running") {
        running = true;
        updateControls();
        playCycle();
      } else {
        context.resume().then(function () {
          if (activeSession !== session || activeLevel !== level) return;
          running = true;
          updateControls();
          playCycle();
        }).catch(function (error) {
          if (activeSession !== session) return;
          status.textContent = "Automatic playback was blocked. Select Play alarm to start it.";
          updateControls();
          console.warn("Flood alert sound could not be enabled.", error);
        });
      }
    } catch (error) {
      if (activeSession === session) {
        status.textContent = "Audio could not be started. Check the device sound settings and select Play sound.";
        updateControls();
      }
      console.warn("Flood alert sound could not be enabled.", error);
    }
  };

  const update = function (monitor, isCurrent) {
    const nextLevel = isCurrent
      && monitor
      && monitor.sensor_status === "online"
      && monitor.source !== "none"
      && (monitor.warning_level === "yellow" || monitor.warning_level === "red")
      ? monitor.warning_level
      : null;
    if (nextLevel === level) return;

    stop();
    level = nextLevel;
    if (!level) {
      panel.hidden = true;
      status.textContent = "";
      return;
    }

    panel.dataset.level = level;
    severity.textContent = level === "red" ? "CRITICAL · ACT NOW" : "MONITOR · BE PREPARED";
    title.textContent = level === "red" ? "Critical flood warning" : "Monitor warning";
    message.textContent = level === "red"
      ? "Move to a safer place and follow official evacuation instructions."
      : "Stay alert and prepare to move if conditions worsen.";
    status.textContent = AudioContextConstructor
      ? "Trying to play the alert. If your browser blocks it, select Play sound."
      : "This browser does not support in-page audio alerts.";
    panel.hidden = false;
    updateControls();
    start();
  };

  playButton.addEventListener("click", start);
  stopButton.addEventListener("click", function () {
    stop();
    panel.hidden = true;
  });
  document.addEventListener("visibilitychange", function () {
    if (document.hidden || !running || !context || context.state === "running") return;
    running = false;
    playing = false;
    window.clearTimeout(timer);
    timer = 0;
    status.textContent = "Audio was paused by the browser. Select Play sound to resume.";
    updateControls();
  });

  window.DaguitanAlertSound = { update: update };
})();
