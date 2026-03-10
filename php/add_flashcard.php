<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: flashcards.php");
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

$userId = (int)$_SESSION['user_id'];
$subjectId = (int)($_POST['subject_id'] ?? 0);
$question = trim($_POST['question'] ?? '');
$answer = trim($_POST['answer'] ?? '');

if ($subjectId <= 0 || $question === '' || $answer === '') {
    header("Location: flashcards.php?subject_id=" . $subjectId);
    exit();
}

/* Verify the subject belongs to this user */
$check = $conn->prepare("SELECT id FROM subjects WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $subjectId, $userId);
$check->execute();
$ok = $check->get_result()->num_rows > 0;
$check->close();

if (!$ok) {
    $conn->close();
    header("Location: flashcards.php");
    exit();
}

/* Insert the flashcard */
$stmt = $conn->prepare("INSERT INTO study_content (subject_id, question, answer) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $subjectId, $question, $answer);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: flashcards.php?subject_id=" . $subjectId);
exit();