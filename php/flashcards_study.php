<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'db';
$user = 'root';
$password = 'root_password';
$db = 'studyguide_db';

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$userId = (int)$_SESSION['user_id'];
$subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if ($subjectId <= 0) {
    $conn->close();
    header("Location: flashcards.php");
    exit();
}

// 1. Define $subjectName here
$check = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $subjectId, $userId);
$check->execute();
$subjectRow = $check->get_result()->fetch_assoc();
$check->close();

if (!$subjectRow) {
    $conn->close();
    die("Not allowed.");
}

$subjectName = $subjectRow['subject_name']; // Line fixing the "Undefined variable $subjectName"

// 2. Define $cards here
$stmt = $conn->prepare("SELECT id, question, answer FROM study_content WHERE subject_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $subjectId);
$stmt->execute();
$res = $stmt->get_result();

$cards = []; // Line fixing the "Undefined variable $cards"
while ($row = $res->fetch_assoc()) {
    $cards[] = [
        "id" => (int)$row["id"],
        "q"  => $row["question"],
        "a"  => $row["answer"]
    ];
}
$stmt->close();
$conn->close();
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Study Mode - Flashcards</title>
  <!-- Link your existing index.css -->
  <link rel="stylesheet" href="CSS/index.css">
  <style>
    /* Navigation Bar Overhaul (Matches Fill-in-Blank) */
    .top-bar {
        background-color: #436EEE;
        width: 100%;
        display: flex;
        align-items: center;
        padding: 12px 30px;
        position: fixed; /* Keeps nav at the top */
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
        margin-left: auto; /* Pushes buttons to the right */
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

    /* Prevents the fixed nav bar from covering your cards */
    body {
        padding-top: 100px;
        background-color: #ffffff !important;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Custom overrides for Study Mode specific elements */
    .study-container {
        width: 95%;
        max-width: 800px;
        margin: 0 auto; /* Centers the card container */
    }
    .study-container {
        width: 95%;
        max-width: 800px;
        margin-top: 20px;
    }

    .flashcard-main {
        min-height: 400px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        border: 2px solid #436EEE;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .flashcard-main:hover {
        background-color: #fcfcfc;
    }

    .card-text {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        padding: 40px;
        white-space: pre-wrap;
    }

    .controls-row {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .btn-study {
        background-color: #436EEE;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        font-size: 1rem;
        transition: background 0.2s;
    }

    .btn-study:hover {
        background-color: #1e3a8a;
    }

    .btn-secondary {
        background-color: #f0f0f0;
        color: #333;
    }

    .meta-info {
        color: #666;
        margin-bottom: 10px;
        font-weight: bold;
    }
    /* Sidebar Score Tracker */
.score-sidebar {
    position: fixed;
    right: 20px;
    top: 100px;
    width: 150px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    z-index: 100;
}

.score-box {
    background: white;
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
    border-top: 5px solid #ccc;
}

.score-box.correct {
    border-color: #2ed573; /* Green */
    color: #2ed573;
}

.score-box.incorrect {
    border-color: #ff4757; /* Red */
    color: #ff4757;
}

.score-num {
    font-size: 2rem;
    font-weight: bold;
    display: block;
}

.score-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: bold;
    color: #666;
}
  </style>
</head>
<body>

<!-- Standard Top Bar to match Index -->
<<nav class="top-bar">
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
<!-- Score Tracker Sidebar -->
<div class="score-sidebar">
    <div class="score-box correct">
        <span class="score-label">Correct</span>
        <span class="score-num" id="correctCount">0</span>
    </div>
    <div class="score-box incorrect">
        <span class="score-label">Wrong</span>
        <span class="score-num" id="wrongCount">0</span>
    </div>
</div>
<div class="study-container">
    <?php if (count($cards) === 0): ?>
        <div class="section-box" style="text-align: center; justify-content: center;">
            <h3>No cards in this deck yet.</h3>
            <p>Add some in Flashcards first.</p>
            <a href="flashcards.php?subject_id=<?php echo $subjectId; ?>" class="tool-btn" style="margin-top: 20px;">Go Add Cards</a>
        </div>
    <?php else: ?>
        
        <div class="meta-info" id="pos">Loading...</div>

        <!-- Main Card Section - Styled as a Section Box -->
        <div class="section-box flashcard-main" id="flipContainer">
            <div class="card-text" id="text"></div>
            <div style="color: #436EEE; font-size: 0.9rem; margin-top: 20px;">(Click to Flip or Press Space)</div>
        </div>

        <!-- Navigation Controls -->
        <div class="controls-row">
            <button type="button" class="btn-study btn-secondary" id="prevBtn">Previous</button>
            <button type="button" class="btn-study" id="flipBtn" style="min-width: 150px;">Show Answer</button>
            <button type="button" class="btn-study btn-secondary" id="nextBtn">Next</button>
        </div>

        <div class="controls-row">
            <button type="button" class="btn-study btn-secondary" id="shuffleBtn" style="font-size: 0.8rem;">Shuffle Deck</button>
            <button type="button" class="btn-study btn-secondary" id="restartBtn" style="font-size: 0.8rem;">Restart</button>
        </div>

        <div class="controls-row">
            <button type="button" class="btn-study" style="background-color: #2ed573;" onclick="markCorrect()">✅ Got It!</button>
            <button type="button" class="btn-study" style="background-color: #ff4757;" onclick="markWrong()">❌ Missed It</button>
        </div>

    <?php endif; ?>
</div>

<script>
    // ... Keeping your original JS logic here ...
    const cards = <?php echo json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    let order = cards.map((_, i) => i);
    let i = 0;
    let showingAnswer = false;

    const posEl = document.getElementById("pos");
    const textEl = document.getElementById("text");
    const flipBtn = document.getElementById("flipBtn");

    function render() {
      const idx = order[i];
      const c = cards[idx];
      posEl.textContent = `Card ${i + 1} of ${order.length}`;
      textEl.textContent = showingAnswer ? c.a : c.q;
      flipBtn.textContent = showingAnswer ? "Show Question" : "Show Answer";
    }

    function flip() {
      showingAnswer = !showingAnswer;
      render();
    }

    function next() {
      showingAnswer = false;
      i = (i + 1) % order.length;
      render();
    }

    function prev() {
      showingAnswer = false;
      i = (i - 1 + order.length) % order.length;
      render();
    }

    function shuffle() {
      for (let j = order.length - 1; j > 0; j--) {
        const k = Math.floor(Math.random() * (j + 1));
        [order[j], order[k]] = [order[k], order[j]];
      }
      i = 0;
      showingAnswer = false;
      render();
    }

    function restart() {
      order = cards.map((_, idx) => idx);
      i = 0;
      showingAnswer = false;
      render();
    }
    let correct = 0;
    let wrong = 0;

    function updateScoreboard() {
        document.getElementById('correctCount').textContent = correct;
        document.getElementById('wrongCount').textContent = wrong;
    }

    function markCorrect() {
        correct++;
        updateScoreboard();
        next(); // Automatically move to next card
    }

    function markWrong() {
        wrong++;
        updateScoreboard();
        next(); // Automatically move to next card
    }

    // Optional: Reset scores when clicking 'Restart'
    function restart() {
        order = cards.map((_, idx) => idx);
        i = 0;
        correct = 0;
        wrong = 0;
        showingAnswer = false;
        updateScoreboard();
        render();
    }

    document.getElementById("flipBtn").addEventListener("click", flip);
    document.getElementById("flipContainer").addEventListener("click", flip); // Clicking card also flips
    document.getElementById("nextBtn").addEventListener("click", next);
    document.getElementById("prevBtn").addEventListener("click", prev);
    document.getElementById("shuffleBtn").addEventListener("click", shuffle);
    document.getElementById("restartBtn").addEventListener("click", restart);

    document.addEventListener("keydown", (e) => {
      if (e.key === " " || e.key === "Enter") flip();
      if (e.key === "ArrowRight") next();
      if (e.key === "ArrowLeft") prev();
    });

    render();
  </script>
</body>
</html>