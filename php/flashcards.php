<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$host = 'db'; $user = 'root'; $password = 'root_password'; $db = 'studyguide_db';
$conn = new mysqli($host, $user, $password, $db);

$userId = (int)$_SESSION['user_id'];
$selectedSubjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

// Fetch Subjects for Sidebar
$subjects = [];
$subResult = $conn->query("SELECT id, subject_name FROM subjects WHERE user_id = $userId ORDER BY subject_name");
while ($row = $subResult->fetch_assoc()) { $subjects[] = $row; }

// Fetch Cards if subject is selected
$cards = [];
$subjectIsOwned = false;
if ($selectedSubjectId > 0) {
    $check = $conn->query("SELECT id FROM subjects WHERE id = $selectedSubjectId AND user_id = $userId");
    if ($check->num_rows > 0) {
        $subjectIsOwned = true;
        $cardResult = $conn->query("SELECT id, question, answer FROM study_content WHERE subject_id = $selectedSubjectId ORDER BY id DESC");
        while ($c = $cardResult->fetch_assoc()) { $cards[] = $c; }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Buddies - Flashcards</title>
    <!-- Link both CSS files -->
    <link rel="stylesheet" href="CSS/index.css">
    <link rel="stylesheet" href="CSS/flashcards.css">
    <style>
    /* Essential layout styles for the sidebar */
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

    .content-area {
        flex-grow: 1 !important;
        min-width: 0;
    }

    .subject-link {
        display: block !important;
        background-color: #436EEE !important;
        color: white !important;
        padding: 10px 15px !important;
        text-decoration: none !important;
        border-radius: 5px !important;
        margin-bottom: 10px !important;
        font-weight: bold !important;
        text-align: center !important;
    }

    .active-subject {
        background-color: #1e3a8a !important;
    }

    .study-mode-btn {
        background-color: #2ed573;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
    } /* <--- THIS BRACKET WAS MISSING![cite: 13, 14] */

    /* Navigation Bar Overhaul (Identical to fill_blank.php) */
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

    body {
        padding-top: 80px; /* Prevents nav from covering content[cite: 14] */
    }
</style>
</head>
<body>
    <!-- FIXED: Nav is now INSIDE the body -->
    <nav class="top-bar">
        <a href="index.php" class="brand-name">Study Buddies</a>
        <div class="nav-spacer">
            <a href="index.php" class="nav-button">Dashboard</a>
            <a href="flashcards.php" class="nav-button">Flashcards</a>
            <a href="fill_blank.php" class="nav-button">Practice</a>
            <a href="multiplechoice.php" class="nav-button">Multiple Choice</a>
            <a href="timer.php" class="nav-button">Timer</a>
            <a href="logout.php" class="nav-button logout-button">Logout</a>
        </div>
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

                    <!-- Flashcards Grid -->
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