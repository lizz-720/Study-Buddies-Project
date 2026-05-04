<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit();
}

$host = 'db';
$user = 'root';
$password = 'root_password';
$db = 'studyguide_db';
$conn = new mysqli($host, $user, $password, $db);

if(isset($_GET['id'])) {
    $event_id = (int)$_GET['id'];
    $current_user = $_SESSION['user_id'];
    
    // Ensure the event belongs to the logged-in user before deleting
    $sql = "DELETE FROM calendar_events WHERE id = $event_id AND user_id = '$current_user'";
    $conn->query($sql);
}

header("Location: index.php");
exit();
?>