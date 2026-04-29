<?php
session_start();

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit();
}

$host = 'db';
$user = 'root';
$password = 'root_password';
$db = 'studyguide_db';

$conn = new mysqli($host, $user, $password, $db);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_text = $conn->real_escape_string($_POST['title']); 
    $event_date = $_POST['event_date'];
    $importance = (int)$_POST['category']; // Matches your INT column[cite: 3]

    $sql = "INSERT INTO calendar_events (user_id, event_text, event_date, importance) 
            VALUES ('$user_id', '$event_text', '$event_date', '$importance')";

    if ($conn->query($sql)) {
        header("Location: index.php");
        exit();
    }
}
?>

<!-- Form in add_event.php -->
<form method="POST">
    <input type="text" name="title" placeholder="Event Title" required>
    <input type="date" name="event_date" required>
    <select name="category" required>
        <option value="1">Project (Green Dot)</option>
        <option value="2">Quiz (Red Dot)</option>
    </select>
    <button type="submit">Save Event</button>
</form>