<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'db'; $user = 'root'; $password = 'root_password'; $db = 'studyguide_db';
$conn = new mysqli($host, $user, $password, $db);
$userId = $_SESSION['user_id'];

// --- NEW: Handle Manual Question Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_question'])) {
    $sub_id = (int)$_POST['subject_id'];
    $q_text = $conn->real_escape_string($_POST['manual_question']);
    $a_text = $conn->real_escape_string($_POST['manual_answer']);
    
    $ins = "INSERT INTO study_content (user_id, subject_id, question, answer) VALUES ('$userId', '$sub_id', '$q_text', '$a_text')";
    $conn->query($ins);
    // Refresh to clear POST data
    header("Location: multiplechoice.php?subject_id=$sub_id");
    exit();
}

$selectedSubjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$subjects = [];
$subResult = $conn->query("SELECT id, subject_name FROM subjects WHERE user_id = '$userId'");
while ($row = $subResult->fetch_assoc()) { $subjects[] = $row; }

$questionData = null;
$options = [];

if ($selectedSubjectId > 0) {
    $stmt = $conn->prepare("SELECT id, question, answer FROM study_content WHERE subject_id = ? ORDER BY RAND() LIMIT 1");
    $stmt->bind_param("i", $selectedSubjectId);
    $stmt->execute();
    $questionData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($questionData) {
        $correctAnswer = $questionData['answer'];
        $options[] = $correctAnswer;
        $distStmt = $conn->prepare("SELECT DISTINCT answer FROM study_content WHERE subject_id = ? AND answer != ? ORDER BY RAND() LIMIT 3");
        $distStmt->bind_param("is", $selectedSubjectId, $correctAnswer);
        $distStmt->execute();
        $distResult = $distStmt->get_result();
        while ($row = $distResult->fetch_assoc()) { $options[] = $row['answer']; }
        $distStmt->close();
        shuffle($options);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Multiple Choice Practice</title>
    <link rel="stylesheet" href="CSS/index.css">
    <style>
        /* Modal Style for Adding Questions */
        #addModal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <nav class="top-bar">
        <div class="nav-center"><h1>Study Buddies Quiz</h1></div>
        <div class="nav-right"><a href="index.php" class="logout-btn">Back to Home</a></div>
    </nav>

    <div class="dashboard" style="display: flex; flex-direction: column; align-items: center;">
        <div class="section-box" style="width: 100%; max-width: 600px;">
            <!-- Header with Plus Sign[cite: 7] -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0;">Multiple Choice Quiz</h3>
                <button onclick="document.getElementById('addModal').style.display='block'" class="add-btn" style="cursor:pointer;">+</button>
            </div>

            <form method="GET" style="margin: 20px 0;">
                <select name="subject_id" required style="padding: 8px; width: 70%;">
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>" <?= ($selectedSubjectId == $subject['id']) ? "selected" : "" ?>>
                            <?= htmlspecialchars($subject['subject_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" style="padding: 8px 15px; cursor: pointer;">Start</button>
            </form>

            <?php if ($questionData): ?>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                <p style="font-size: 1.1rem; margin-bottom: 20px;"><strong>Question:</strong> <?= htmlspecialchars($questionData['question']) ?></p>

                <form id="quizForm">
                    <?php foreach ($options as $opt): ?>
                        <label style="display: block; background: #f9f9f9; padding: 12px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #ddd; cursor: pointer;">
                            <input type="radio" name="user_choice" value="<?= htmlspecialchars($opt) ?>" required>
                            <?= htmlspecialchars($opt) ?>
                        </label>
                    <?php endforeach; ?>
                    <button type="button" onclick="checkQuizAnswer('<?= addslashes($questionData['answer']) ?>')" 
                            style="width: 100%; background: #436EEE; color: white; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px;">
                        Submit Answer
                    </button>
                </form>
                <div id="quizFeedback" style="margin-top: 20px; text-align: center; font-weight: bold;"></div>
                <button onclick="location.reload()" id="nextBtn" style="display:none; width:100%; margin-top:10px; padding: 10px; cursor:pointer;">Next Question</button>
            <?php endif; ?>
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

    <script>
    function checkQuizAnswer(correct) {
        const selected = document.querySelector('input[name="user_choice"]:checked');
        const feedback = document.getElementById('quizFeedback');
        const nextBtn = document.getElementById('nextBtn');
        if(!selected) return;
        if(selected.value === correct) {
            feedback.innerHTML = "✅ Correct!";
            feedback.style.color = "#2ed573";
        } else {
            feedback.innerHTML = "❌ Incorrect. Answer: " + correct;
            feedback.style.color = "#ff4757";
        }
        nextBtn.style.display = "block";
    }
    </script>
</body>
</html>