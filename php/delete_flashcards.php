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
$cardId = (int)($_POST['card_id'] ?? 0);
$subjectId = (int)($_POST['subject_id'] ?? 0);

if ($cardId <= 0) {
    header("Location: flashcards.php?subject_id=" . $subjectId);
    exit();
}

/* Delete only if the card belongs to a subject owned by this user */
$stmt = $conn->prepare("
    DELETE sc FROM study_content sc
    JOIN subjects s ON s.id = sc.subject_id
    WHERE sc.id = ? AND s.user_id = ?
");
$stmt->bind_param("ii", $cardId, $userId);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: flashcards.php?subject_id=" . $subjectId);
exit();