<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Study Timer</title>
  <style>
    body { font-family: sans-serif; max-width: 700px; margin: 2em auto; }
    .box { border: 1px solid #ddd; padding: 20px; border-radius: 10px; }
    .time { font-size: 64px; font-weight: 700; margin: 20px 0; }
    button { padding: 10px 14px; margin-right: 8px; cursor: pointer; }
    input { width: 80px; padding: 8px; }
    .row { margin: 10px 0; }
  </style>
</head>
<body>

<h1>⏱️ Study Timer</h1>
<a href="index.php"><button type="button">Back to Home</button></a>

<div class="box">
  <div class="row">
    <label>Study (min): <input id="studyMins" type="number" min="1" value="25"></label>
    <label style="margin-left:12px;">Break (min): <input id="breakMins" type="number" min="1" value="5"></label>
  </div>

  <div class="row">
    <button id="studyBtn" type="button">Study Mode</button>
    <button id="breakBtn" type="button">Break Mode</button>
  </div>

  <div class="time" id="timeDisplay">25:00</div>
  <div id="modeLabel">Mode: Study</div>

  <div class="row" style="margin-top:16px;">
    <button id="startBtn" type="button">Start</button>
    <button id="pauseBtn" type="button">Pause</button>
    <button id="resetBtn" type="button">Reset</button>
  </div>

  <p id="status"></p>
</div>

<script>
  // state
  let mode = "study";                 // "study" or "break"
  let remainingSeconds = 25 * 60;     // countdown
  let intervalId = null;              // stores the timer interval

  // elements
  const timeDisplay = document.getElementById("timeDisplay");
  const modeLabel = document.getElementById("modeLabel");
  const statusEl = document.getElementById("status");

  const studyMinsInput = document.getElementById("studyMins");
  const breakMinsInput = document.getElementById("breakMins");

  const startBtn = document.getElementById("startBtn");
  const pauseBtn = document.getElementById("pauseBtn");
  const resetBtn = document.getElementById("resetBtn");
  const studyBtn = document.getElementById("studyBtn");
  const breakBtn = document.getElementById("breakBtn");

  // helpers
  function formatTime(totalSeconds) {
    const m = Math.floor(totalSeconds / 60);
    const s = totalSeconds % 60;
    return String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
  }

  function setMode(newMode) {
    mode = newMode;
    modeLabel.textContent = "Mode: " + (mode === "study" ? "Study" : "Break");
    statusEl.textContent = "";
    resetTimer(); // reset to new mode's minutes
  }

  function getModeSeconds() {
    const mins = mode === "study"
      ? parseInt(studyMinsInput.value || "25", 10)
      : parseInt(breakMinsInput.value || "5", 10);

    return Math.max(1, mins) * 60;
  }

  function render() {
    timeDisplay.textContent = formatTime(remainingSeconds);
  }

  function stopInterval() {
    if (intervalId !== null) {
      clearInterval(intervalId);
      intervalId = null;
    }
  }

  // core actions
  function startTimer() {
    if (intervalId !== null) return; // already running
    statusEl.textContent = "Running...";
    intervalId = setInterval(() => {
      remainingSeconds--;

      if (remainingSeconds <= 0) {
        remainingSeconds = 0;
        render();
        stopInterval();

        // simple alert + auto switch
        if (mode === "study") {
          alert("Study done! Time for a break.");
          setMode("break");
        } else {
          alert("Break done! Back to studying.");
          setMode("study");
        }
        return;
      }
      render();
    }, 1000);
  }

  function pauseTimer() {
    stopInterval();
    statusEl.textContent = "Paused.";
  }

  function resetTimer() {
    stopInterval();
    remainingSeconds = getModeSeconds();
    statusEl.textContent = "Reset.";
    render();
  }

  // events
  startBtn.addEventListener("click", startTimer);
  pauseBtn.addEventListener("click", pauseTimer);
  resetBtn.addEventListener("click", resetTimer);

  studyBtn.addEventListener("click", () => setMode("study"));
  breakBtn.addEventListener("click", () => setMode("break"));

  // if user changes minutes, reset display to match
  studyMinsInput.addEventListener("change", () => { if (mode === "study") resetTimer(); });
  breakMinsInput.addEventListener("change", () => { if (mode === "break") resetTimer(); });

  // initial render
  render();
</script>

</body>
</html>