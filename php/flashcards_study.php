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

// verify subject belongs to user
$check = $conn->prepare("SELECT subject_name FROM subjects WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $subjectId, $userId);
$check->execute();
$subjectRow = $check->get_result()->fetch_assoc();
$check->close();

if (!$subjectRow) {
  $conn->close();
  die("Not allowed.");
}

$subjectName = $subjectRow['subject_name'];

// load all cards
$stmt = $conn->prepare("SELECT id, question, answer FROM study_content WHERE subject_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $subjectId);
$stmt->execute();
$res = $stmt->get_result();

$cards = [];
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
  <style>
    body { font-family: sans-serif; max-width: 800px; margin: 2em auto; }
    .card { border:1px solid #ddd; border-radius:12px; padding:18px; min-height:160px; }
    .meta { color:#666; margin: 10px 0; }
    .big { font-size: 22px; white-space: pre-wrap; }
    .row { display:flex; gap:10px; margin-top: 12px; flex-wrap: wrap; }
    button { padding:10px 14px; cursor:pointer; }
  </style>
</head>
<body>

<a href="index.php">
    <button type="button">🏠 Home</button>
</a>

<a href="flashcards.php?subject_id=<?php echo $subjectId; ?>">
    <button type="button">⬅ Back to Deck</button>
</a>

<br><br>

<h2>Study Mode: <?php echo htmlspecialchars($subjectName); ?></h2>
<p class="meta">Deck ID: <?php echo $subjectId; ?> • Cards: <?php echo count($cards); ?></p>

<?php if (count($cards) === 0): ?>
  <p>No cards in this deck yet. Add some in Flashcards first.</p>
  <a href="flashcards.php?subject_id=<?php echo $subjectId; ?>"><button type="button">Go Add Cards</button></a>
<?php else: ?>

  <div class="card">
    <div class="meta" id="pos"></div>
    <div class="big" id="text"></div>
  </div>

  <div class="row">
    <button type="button" id="flipBtn">Show Answer</button>
    <button type="button" id="prevBtn">Prev</button>
    <button type="button" id="nextBtn">Next</button>
    <button type="button" id="shuffleBtn">Shuffle</button>
    <button type="button" id="restartBtn">Restart</button>
  </div>

  <script>
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
      posEl.textContent = `Card ${i + 1} / ${order.length}`;
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
      // shuffle
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

    document.getElementById("flipBtn").addEventListener("click", flip);
    document.getElementById("nextBtn").addEventListener("click", next);
    document.getElementById("prevBtn").addEventListener("click", prev);
    document.getElementById("shuffleBtn").addEventListener("click", shuffle);
    document.getElementById("restartBtn").addEventListener("click", restart);

    // keyboard shortcuts
    document.addEventListener("keydown", (e) => {
      if (e.key === " " || e.key === "Enter") flip();
      if (e.key === "ArrowRight") next();
      if (e.key === "ArrowLeft") prev();
    });

    render();
  </script>

<?php endif; ?>

</body>
</html>