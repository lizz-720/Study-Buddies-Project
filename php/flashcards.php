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
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Buddies - Flashcards</title>
    <link rel="stylesheet" href="CSS/index.css">
    <link rel="stylesheet" href="CSS/flashcards.css">
    <style>
    

/* 1. Task Bar: Evenly spaced links */
.top-bar {
    display: flex;
    justify-content: flex-start; /* Groups items at the start */
    align-items: center;
    padding: 10px 20px;
    background-color: #436EEE !important;
    border-bottom: 1px solid #2c52bd;
    color: white;
    gap: 20px; /* Adjust this value to increase/decrease spacing between buttons */
}

/* Base style for all nav buttons */
.nav-btn {
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    border: none;
    transition: opacity 0.2s;
}

/* White buttons (Dashboard, Fill-in-Blank, etc.) */
.btn-white {
    background-color: white !important;
    color: #436EEE !important;
}

/* Red button (Logout) */
.btn-red {
    background-color: #ff4757 !important;
    color: white !important;
    margin-left: auto; /* Keeps logout on the far right if desired */
}

.nav-btn:hover {
    opacity: 0.9;
}
/* 2. Fix the Shrinking Box */
.content-area {
    flex-grow: 1 !important; /* Forces it to take all remaining space */
    min-width: 0; /* Prevents flex items from overflowing or shrinking weirdly */
}

.section-box {
    width: 100% !important; /* Ensures the box inside the content area stays wide */
    box-sizing: border-box;
}

/* Study Mode Button style (added for completeness) */
.study-mode-btn {
    background-color: #2ed573;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
}

    /* 3. LAYOUT & SIDEBAR (Keeping your previous fixes) */
    .flashcard-layout-wrapper {
        display: flex !important;
        width: 100% !important;
        gap: 20px !important;
        padding: 20px !important;
        box-sizing: border-box !important;
        align-items: flex-start !important;
    }

    .subject-sidebar {
        width: 250px !important;
        flex-shrink: 0 !important;
    }

    /* Subject Buttons in Sidebar (Staying Blue) */
    .subject-link, .subject-link:visited {
        display: block !important;
        background-color: #436EEE !important;
        color: white !important;
        padding: 10px 15px !important;
        text-decoration: none !important;
        border-radius: 5px !important;
        text-align: center !important;
        font-weight: bold !important;
        margin-bottom: 10px !important;
        border: none !important;
    }

    .active-subject {
        background-color: #1e3a8a !important; /* Darker blue for selected */
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.3) !important;
    }
</style>

<!-- TOP NAVIGATION BAR -->
<nav class="top-bar">
    <h1 style="margin: 0 20px 0 0; font-size: 1.5rem; color: white;">Study Buddies</h1>
    <a href="index.php" class="nav-btn">🏠 Dashboard</a>
    <a href="fill_blank.php" class="nav-btn">Fill-in-Blank</a>
    <a href="multiplechoice.php" class="nav-btn">Multiple Choice</a>
    <!-- Push Logout to the far right if desired, otherwise it stays left with the others -->
    <a href="logout.php" class="nav-btn" style="margin-left: auto; background-color: #ffffff !important; color: #ff4757 !important;">Logout</a>
</nav>
    <div class="flashcard-layout-wrapper">
        
        <!-- SIDEBAR -->
        <div class="section-box subject-sidebar">
            <h3>📖 My Subjects</h3>
            <div class="subject-list">
                <?php foreach ($subjects as $subject): ?>
                    <a href="flashcards.php?subject_id=<?php echo (int)$subject['id']; ?>" 
                       class="subject-link <?php echo ($selectedSubjectId === (int)$subject['id']) ? 'active-subject' : ''; ?>">
                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="content-area">
            <?php if ($selectedSubjectId > 0 && $subjectIsOwned): ?>
                <div class="section-box" style="width: 100%; min-height: 500px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h2 style="margin: 0;">🃏 Deck: <?php echo count($cards); ?> Cards</h2>
                        <a href="flashcards_study.php?subject_id=<?php echo $selectedSubjectId; ?>">
                            <button type="button" class="study-mode-btn">Start Study Mode</button>
                        </a>
                    </div>

                    <!-- Add Card Section -->
                    <div class="add-card-section" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                        <h4 style="margin-top: 0;">Add New Card</h4>
                        <form method="POST" action="add_flashcard.php">
                            <input type="hidden" name="subject_id" value="<?php echo $selectedSubjectId; ?>">
                            <textarea name="question" placeholder="Question" required style="width: 100%; margin-bottom: 10px;"></textarea>
                            <textarea name="answer" placeholder="Answer" required style="width: 100%; margin-bottom: 10px;"></textarea>
                            <button type="submit" style="background-color: #436EEE; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Add Card</button>
                        </form>
                    </div>

                    <!-- Dropdown for Existing Cards -->
                    <details>
                        <summary style="font-weight: bold; cursor: pointer; color: #436EEE; margin-bottom: 15px;">
                            ▼ View Existing Flashcards
                        </summary>
                        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: flex-start;">
                            <?php foreach ($cards as $card): ?>
                                <div class="flashcard-container">
                                    <div class="flashcard-wrapper" onclick="this.querySelector('.flashcard-inner').classList.toggle('is-flipped')">
                                        <div class="flashcard-inner">
                                            <div class="card-face card-face-front">
                                                <p><?php echo htmlspecialchars($card['question']); ?></p>
                                            </div>
                                            <div class="card-face card-face-back">
                                                <p><?php echo htmlspecialchars($card['answer']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </details>
                </div>
            <?php else: ?>
                <div class="section-box">
                    <h3>Select a subject on the left to start.</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>