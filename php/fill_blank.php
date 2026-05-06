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
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$selectedSubjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

$subjects = [];
$result = $conn->query("SELECT id, subject_name FROM subjects WHERE user_id = '$userId'");
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

$questionData = null;
if ($selectedSubjectId > 0) {
    $stmt = $conn->prepare("SELECT id, question, answer FROM study_content WHERE subject_id = ? ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $selectedSubjectId);
    $stmt->execute();
    $questionData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Practice Mode | Study Buddies</title>
    <link rel="stylesheet" href="CSS/index.css">
    <style>
        /* General Page Styling */
        body {
            background-color: #ffffff !important; /* Pure white background */
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navigation Bar Overhaul */
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
        }

        .nav-spacer {
            margin-left: auto; /* Pushes everything following it to the right */
            display: flex;
            gap: 12px;
        }

        /* Button-style Links */
        .nav-button {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
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
            color: #436EEE;
        }

        .logout-button {
            background-color: #ff4757; /* Solid Red */
            border: none;
        }

        .logout-button:hover {
            background-color: #ff6b81;
            color: white;
        }

        /* Content Area */
        .main-content {
            margin-top: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .practice-card {
            background-color: #f8f9fa;
            border: 1px solid #e1e4e8;
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 700px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            text-align: center;
        }

        .question-display {
            font-size: 1.5rem;
            color: #2d3436;
            margin-bottom: 30px;
            line-height: 1.4;
        }

        .answer-textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #dfe6e9;
            border-radius: 10px;
            font-size: 1.1rem;
            margin-bottom: 20px;
            resize: vertical;
            min-height: 100px;
            box-sizing: border-box;
        }

        .submit-btn {
            background-color: #436EEE;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Right-Aligned Navigation -->
    <nav class="top-bar">
        <h1 class="brand-name">Study Buddies</h1>
        <div class="nav-spacer">
            <a href="index.php" class="nav-button">Dashboard</a>
            <a href="flashcards.php" class="nav-button">Flashcards</a>
            <a href="multiplechoice.php" class="nav-button">Multiple Choice</a>
            <a href="timer.php" class="nav-button">Timer</a>
            <a href="logout.php" class="nav-button logout-button">Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="practice-card">
            <h1>Short Answer Practice</h1>

            <!-- Subject Picker -->
            <form method="GET" style="margin-bottom: 30px;">
                <select name="subject_id" onchange="this.form.submit()" style="padding: 12px; border-radius: 8px; border: 1px solid #ccc; width: 280px;">
                    <option value="">-- Select Subject to Start --</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>" <?= ($selectedSubjectId == $subject['id']) ? "selected" : "" ?>>
                            <?= htmlspecialchars($subject['subject_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($questionData && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                <!-- Question View -->
                <form method="POST">
                    <p class="question-display"><?= htmlspecialchars($questionData['question']) ?></p>
                    <textarea name="user_answer" class="answer-textarea" placeholder="Type your answer here..." required></textarea>
                    <input type="hidden" name="correct_answer" value="<?= htmlspecialchars($questionData['answer']) ?>">
                    <button type="submit" class="submit-btn">Check My Answer</button>
                </form>
            <?php endif; ?>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_answer'])) {
                $correct = strtolower(trim($_POST['correct_answer']));
                $userAnswer = strtolower(trim($_POST['user_answer']));
                $isCorrect = ($correct === $userAnswer);

                echo "<div style='margin-top: 20px; padding: 20px; border-radius: 10px; background-color: " . ($isCorrect ? "#d4edda" : "#f8d7da") . ";'>";
                if ($isCorrect) {
                    echo "<h2 style='color: #155724;'>Correct! 🥳</h2>";
                } else {
                    echo "<h2 style='color: #721c24;'>Keep Trying!</h2>";
                    echo "<p>The correct answer was: <strong>" . htmlspecialchars($_POST['correct_answer']) . "</strong></p>";
                }
                echo "<a href='fill_blank.php?subject_id=$selectedSubjectId' class='submit-btn' style='display:inline-block; text-decoration:none; margin-top:15px;'>Next Question</a>";
                echo "</div>";
            }
            ?>
        </div>

    </div>
    <!-- Modal for Quick Adding Questions -->
    <div id="addModal">
        <div class="modal-content">
            <h4>Add a New Question</h4>
            <form method="POST" style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                <select name="subject_id" required style="padding: 8px;">
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="manual_question" placeholder="Enter Question" required style="padding: 8px;">
                <input type="text" name="manual_answer" placeholder="Enter Correct Answer" required style="padding: 8px;">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="flex: 1; background: #2ed573; color: white; border: none; padding: 10px; border-radius: 5px;">Save</button>
                    <button type="button" onclick="document.getElementById('addModal').style.display='none'" style="flex: 1; background: #ff4757; color: white; border: none; padding: 10px; border-radius: 5px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>