<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Timer - Study Buddies</title>
    <!-- Link your existing index.css here -->
    <link rel="stylesheet" href="CSS/index.css">
    <style>
      <style>
        /* 1. Navigation Bar Overhaul (Matches Fill-in-Blank)[cite: 17] */
        .top-bar {
            background-color: #436EEE;
            width: 100%;
            display: flex;
            align-items: center;
            padding: 12px 30px;
            position: fixed; 
            top: 0;
            left: 0;
            z-index: 1000;
            box-sizing: border-box;
        }

        .brand-name {
            font-size: 1.6rem;
            color: white;
            margin: 0;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-spacer {
            margin-left: auto; /* Pushes buttons to the right[cite: 17] */
            display: flex;
            gap: 12px;
        }

        .nav-button {
            background-color: rgba(255, 255, 255, 0.2);
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .nav-button:hover {
            background-color: white;
            color: #436EEE !important;
        }

        .logout-button {
            background-color: #ff4757 !important;
            border: none;
        }

        /* 2. Global Page Styling[cite: 17] */
        body {
            
            background-color: #ffffff !important;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* 3. Timer Specific Layout[cite: 17] */
        .timer-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 20px;
        }

        .timer-box {
            max-width: 600px;
            width: 100%;
            text-align: center;
            padding: 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.05);
        }

        .time-display {
            font-size: 5rem;
            font-weight: bold;
            color: #436EEE;
            margin: 20px 0;
            font-family: 'Courier New', Courier, monospace;
        }

        .mode-status {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
        }

        .input-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .timer-input {
            width: 80px;
            padding: 10px;
            border: 2px solid #eee;
            border-radius: 8px;
            text-align: center;
            font-size: 1.1rem;
        }

        .btn-timer {
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.1s;
        }

        .btn-timer:active { transform: scale(0.95); }
        .btn-blue { background-color: #436EEE; color: white; }
        .btn-green { background-color: #2ed573; color: white; }
        .btn-red { background-color: #ff4757; color: white; }
        .btn-gray { background-color: #f0f0f0; color: #333; }

        .button-row {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

<!-- Standard Navigation Bar -->
<nav class="top-bar">
    <a href="index.php" class="brand-name">Study Buddies</a>
    <div class="nav-spacer">
        <a href="index.php" class="nav-button">Dashboard</a>
        <a href="flashcards.php" class="nav-button">Flashcards</a>
        <a href="fill_blank.php" class="nav-button">Fill-in-Blank</a>
        <a href="multiplechoice.php" class="nav-button">Multiple Choice</a>
        <a href="timer.php" class="nav-button">Timer</a>
        <a href="logout.php" class="nav-button logout-button">Logout</a>
    </div>
</nav>

<div class="timer-container">
    <div class="section-box timer-box">
        <h2 style="margin-top: 0; color: #436EEE;">⏱️ Study Timer</h2>
        
        <!-- Configuration -->
        <div class="input-group">
            <div class="input-item">
                <label>Study (min)</label>
                <input id="studyMins" class="timer-input" type="number" min="1" value="25">
            </div>
            <div class="input-item">
                <label>Break (min)</label>
                <input id="breakMins" class="timer-input" type="number" min="1" value="5">
            </div>
        </div>

        <!-- Mode Toggle -->
        <div class="button-row">
            <button id="studyBtn" class="btn-timer btn-blue" type="button">Study Mode</button>
            <button id="breakBtn" class="btn-timer btn-gray" type="button">Break Mode</button>
        </div>

        <div class="time-display" id="timeDisplay">25:00</div>
        <div class="mode-status" id="modeLabel">Current: Studying</div>

        <!-- Controls -->
        <div class="button-row" style="margin-top:30px;">
            <button id="startBtn" class="btn-timer btn-green" type="button">▶ Start</button>
            <button id="pauseBtn" class="btn-timer btn-gray" type="button">⏸ Pause</button>
            <button id="resetBtn" class="btn-timer btn-red" type="button">🔄 Reset</button>
        </div>

        <p id="status" style="margin-top: 20px; font-style: italic; color: #666;"></p>
    </div>
</div>

<script>
    // [Keeping your original logic as it works perfectly]
    let mode = "study";
    let remainingSeconds = 25 * 60;
    let intervalId = null;

    const timeDisplay = document.getElementById("timeDisplay");
    const modeLabel = document.getElementById("modeLabel");
    const statusEl = document.getElementById("status");
    const studyMinsInput = document.getElementById("studyMins");
    const breakMinsInput = document.getElementById("breakMins");

    function formatTime(totalSeconds) {
        const m = Math.floor(totalSeconds / 60);
        const s = totalSeconds % 60;
        return String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
    }

    function setMode(newMode) {
        mode = newMode;
        modeLabel.textContent = "Current: " + (mode === "study" ? "Studying" : "On Break");
        
        // Update UI colors for modes
        const studyBtn = document.getElementById("studyBtn");
        const breakBtn = document.getElementById("breakBtn");
        if(mode === "study") {
            studyBtn.className = "btn-timer btn-blue";
            breakBtn.className = "btn-timer btn-gray";
        } else {
            studyBtn.className = "btn-timer btn-gray";
            breakBtn.className = "btn-timer btn-blue";
        }
        
        statusEl.textContent = "";
        resetTimer();
    }

    function getModeSeconds() {
        const mins = mode === "study" ? parseInt(studyMinsInput.value || "25") : parseInt(breakMinsInput.value || "5");
        return Math.max(1, mins) * 60;
    }

    function render() {
        timeDisplay.textContent = formatTime(remainingSeconds);
    }

    function startTimer() {
        if (intervalId !== null) return;
        statusEl.textContent = "Timer is running...";
        intervalId = setInterval(() => {
            remainingSeconds--;
            if (remainingSeconds <= 0) {
                remainingSeconds = 0;
                render();
                clearInterval(intervalId);
                intervalId = null;
                alert(mode === "study" ? "Study session finished!" : "Break over! Ready to study?");
                setMode(mode === "study" ? "break" : "study");
                return;
            }
            render();
        }, 1000);
    }

    function pauseTimer() {
        clearInterval(intervalId);
        intervalId = null;
        statusEl.textContent = "Timer paused.";
    }

    function resetTimer() {
        clearInterval(intervalId);
        intervalId = null;
        remainingSeconds = getModeSeconds();
        statusEl.textContent = "Timer reset.";
        render();
    }

    document.getElementById("startBtn").addEventListener("click", startTimer);
    document.getElementById("pauseBtn").addEventListener("click", pauseTimer);
    document.getElementById("resetBtn").addEventListener("click", resetTimer);
    document.getElementById("studyBtn").addEventListener("click", () => setMode("study"));
    document.getElementById("breakBtn").addEventListener("click", () => setMode("break"));
</script>
</body>
</html>