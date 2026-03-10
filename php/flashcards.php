<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$host = 'db';
$user = 'root';
$password = 'root_password';
$db = 'studyguide_db';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('connection failed: ' . $conn->connect_error);
}

$userId = (int)$_SESSION['user_id'];
$selectedSubjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$error = '';

$subjectColumn = 'subject_name';

$subjects = [];
if ($subjectColumn !== null) {
    $deckSql = "SELECT id, {$subjectColumn} AS subject_name FROM subjects WHERE user_id = ? ORDER BY {$subjectColumn}";
    $deckStmt = $conn->prepare($deckSql);
    $deckStmt->bind_param('i', $userId);
    $deckStmt->execute();
    $deckResult = $deckStmt->get_result();
    while ($deck = $deckResult->fetch_assoc()) {
        $subjects[] = $deck;
    }
    $deckStmt->close();
} else {
    $error = 'Unable to load subjects because no supported subject name column was found.';
}

$subjectIsOwned = false;
$cards = [];
if ($selectedSubjectId > 0 && $subjectColumn !== null) {
    $ownershipSql = 'SELECT id FROM subjects WHERE id = ? AND user_id = ?';
    $ownershipStmt = $conn->prepare($ownershipSql);
    $ownershipStmt->bind_param('ii', $selectedSubjectId, $userId);
    $ownershipStmt->execute();
    $ownershipResult = $ownershipStmt->get_result();
    $subjectIsOwned = $ownershipResult->num_rows > 0;
    $ownershipStmt->close();

    if ($subjectIsOwned) {
        $cardsSql = 'SELECT id, question, answer FROM study_content WHERE subject_id = ? ORDER BY id DESC';
        $cardsStmt = $conn->prepare($cardsSql);
        $cardsStmt->bind_param('i', $selectedSubjectId);
        $cardsStmt->execute();
        $cardsResult = $cardsStmt->get_result();
        while ($card = $cardsResult->fetch_assoc()) {
            $cards[] = $card;
        }
        $cardsStmt->close();
    } else {
        $error = 'You are not allowed to access that subject.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flashcards</title>
</head>
<body>
    <h1>Flashcards</h1>
    <a href="index.php"><button type="button">Back to Home</button></a>

    <?php if ($error !== ''): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="GET" action="flashcards.php" style="margin: 20px 0;">
        <label for="subject_id">Choose a Subject:</label>
        <select id="subject_id" name="subject_id" required>
            <option value="">-- Select Subject --</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?php echo (int)$subject['id']; ?>" <?php echo ($selectedSubjectId === (int)$subject['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Load Deck</button>
    </form>

<?php if ($selectedSubjectId > 0 && $subjectIsOwned): ?>
    <a href="flashcards_study.php?subject_id=<?php echo $selectedSubjectId; ?>">
        <button type="button">Study Mode</button>
    </a>
    <h2>Add Flashcard</h2>
        <form method="POST" action="add_flashcard.php" style="margin-bottom: 30px;">
            <input type="hidden" name="subject_id" value="<?php echo $selectedSubjectId; ?>">
            <div>
                <label for="question">Question</label><br>
                <textarea id="question" name="question" rows="4" cols="60" required></textarea>
            </div>
            <div>
                <label for="answer">Answer</label><br>
                <textarea id="answer" name="answer" rows="4" cols="60" required></textarea>
            </div>
            <button type="submit">Add Flashcard</button>
        </form>

        <h2>Existing Flashcards</h2>
        <?php if (count($cards) === 0): ?>
            <p>No existing flashcards for this subject.</p>
        <?php else: ?>
            <?php foreach ($cards as $card): ?>
                <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                    <p><strong>Q:</strong> <?php echo nl2br(htmlspecialchars($card['question'])); ?></p>
                    <p><strong>A:</strong> <?php echo nl2br(htmlspecialchars($card['answer'])); ?></p>
                    <form method="POST" action="delete_flashcard.php">
                        <input type="hidden" name="card_id" value="<?php echo (int)$card['id']; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo $selectedSubjectId; ?>">
                        <button type="submit">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>