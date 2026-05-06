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
<html>
<head>
    <title>Fill in the Blank</title>
    <link rel="stylesheet" href="CSS/index.css"> <!-- Main Nav Styles -->
    <link rel="stylesheet" href="CSS/fill_blank.css"> <!-- New Styles -->
</head>
<body>

    <!-- Navigation Tab Bar -->
    <nav class="top-bar" style="background-color: white; width: 100%; display: flex; justify-content: center; gap: 15px; padding: 15px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: fixed; top: 0; left: 0; z-index: 1000;">
        <a href="index.php" style="text-decoration: none;"><button type="button" style="background-color: #436EEE; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Home</button></a>
        <a href="flashcards.php" style="text-decoration: none;"><button type="button" style="background-color: #436EEE; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Flashcards</button></a>
        <a href="timer.php" style="text-decoration: none;"><button type="button" style="background-color: #436EEE; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Study Timer</button></a>
        <a href="logout.php" style="text-decoration: none;"><button type="button" style="background-color: #ff4757; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Logout</button></a>
    </nav>

<!-- Add a spacer so the content doesn't hide under the fixed nav -->
<div style="margin-top: 80px;"></div>

<div class="practice-container">
    <!-- Rest of your content remains here -->
    <h1>Fill in the Blank</h1>

    <form method="GET" style="margin-bottom: 20px;">
        <select name="subject_id" required>
            <option value="">-- Select Subject --</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>" <?= ($selectedSubjectId == $subject['id']) ? "selected" : "" ?>>
                    <?= htmlspecialchars($subject['subject_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Start</button>
    </form>

    <?php if ($questionData): 
        // 1. Get the question and answer[cite: 12]
        $fullSentence = htmlspecialchars($questionData['question']);
        $answer = htmlspecialchars($questionData['answer']);

        // 2. Create the input HTML[cite: 11, 12]
        $inputField = '<input type="text" name="user_answer" class="blank-input" required autocomplete="off">';

        // 3. Replace the answer word in the sentence with the input field[cite: 12]
        // Note: This assumes the word to be blanked matches the 'answer' column exactly.
        $displaySentence = str_ireplace($answer, $inputField, $fullSentence);
    ?>

        <form method="POST">
            <div class="sentence-box">
                <?= $displaySentence ?>
            </div>
            <input type="hidden" name="correct_answer" value="<?= $answer ?>">
            <button type="submit">Check Answer</button>
        </form>

    <?php endif; ?>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $correct = strtolower(trim($_POST['correct_answer']));
        $userAnswer = strtolower(trim($_POST['user_answer']));

        if ($correct === $userAnswer) {
            echo "<p style='color:green; font-weight:bold;'>Correct! 🎉</p>";
        } else {
            echo "<p style='color:red;'>Incorrect. The word was: <strong>" . htmlspecialchars($_POST['correct_answer']) . "</strong></p>";
        }
    }
    ?>
    
    <div style="margin-top: 20px;">
        <a href="index.php" style="color: #436EEE; text-decoration: none;">Back to Dashboard</a>
    </div>
</div>

</body>
</html>