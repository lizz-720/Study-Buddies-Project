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
</head>
<body>

<a href="index.php"><button type="button">Home</button></a>
<a href="flashcards.php"><button type="button">Flashcards</button></a>
<a href="fill_blank.php"><button type="button">Fill in the Blank</button></a>
<a href="timer.php"><button type="button">Study Timer</button></a>
<a href="logout.php"><button type="button">Logout</button></a>

<h1>Fill in the Blank Practice</h1>

<form method="GET">
    <select name="subject_id" required>
        <option value="">-- Select Subject --</option>
        <?php foreach ($subjects as $subject): ?>
            <option value="<?= $subject['id'] ?>" <?= ($selectedSubjectId == $subject['id']) ? "selected" : "" ?>>
                <?= htmlspecialchars($subject['subject_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Start Practice</button>
</form>

<?php if ($questionData): ?>
    <hr>
    <h3><?= htmlspecialchars($questionData['question']) ?></h3>

    <form method="POST">
        <input type="hidden" name="correct_answer" value="<?= htmlspecialchars($questionData['answer']) ?>">
        <input type="text" name="user_answer" placeholder="Your answer..." required>
        <button type="submit">Check Answer</button>
    </form>
<?php endif; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correct = strtolower(trim($_POST['correct_answer']));
    $userAnswer = strtolower(trim($_POST['user_answer']));

    if ($correct === $userAnswer) {
        echo "<p style='color:green;'>Correct! 🎉</p>";
    } else {
        echo "<p style='color:red;'>Incorrect. Correct answer: " . htmlspecialchars($_POST['correct_answer']) . "</p>";
    }
}
?>

</body>
</html>