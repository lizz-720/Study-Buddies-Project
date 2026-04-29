<?php
session_start();
if(!isset($_SESSION['user_id'])) exit();

$conn = new mysqli('db', 'root', 'root_password', 'studyguide_db');
$subject_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Ensure users can only delete their own subjects
$conn->query("DELETE FROM subjects WHERE id = '$subject_id' AND user_id = '$user_id'");

header("Location: index.php");
exit();
?>