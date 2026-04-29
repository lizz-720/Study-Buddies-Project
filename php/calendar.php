<?php
session_start();

// Database connection details 
$host = 'db';
$user = 'root';
$password = 'root_password';
$db = 'studyguide_db';

$conn = new mysqli($host, $user, $password, $db);

// 1. Logic Section: Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $date = $conn->real_escape_string($_POST['event_date']);
    $text = $conn->real_escape_string($_POST['event_text']);
    $importance = (int)$_POST['importance'];
    $user_id = $_SESSION['user_id'];

    $conn->query("INSERT INTO calendar_events (user_id, event_date, event_text, importance) 
                  VALUES ('$user_id', '$date', '$text', '$importance')");
    
    // Refresh to update the list
    header("Location: calendar.php");
    exit();
}

// 2. Logic Section: Prepare Sorted Data
// This orders by the closest date first, then by highest importance
$query = "SELECT event_date, event_text, importance 
          FROM calendar_events 
          WHERE user_id = '{$_SESSION['user_id']}' 
          ORDER BY event_date ASC, importance DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>StudyBuddy Calendar</title>
    <link rel="stylesheet" href="calendar.css">
</head>
<body>
    <h1>StudyBuddy Calendar</h1>

    <form method="POST" action="calendar.php">
        <input type="date" name="event_date" required>
        <input type="text" name="event_text" placeholder="Assignment name..." required>
        <select name="importance">
            <option value="3">High Importance (Test)</option>
            <option value="2">Medium Importance (Quiz)</option>
            <option value="1">Low Importance (Assignment)</option>
        </select>
        <button type="submit" name="add_event">Add to Calendar</button>
    </form>

    <h2>Sorted Events</h2>
    <ul>
        <?php while($row = $result->fetch_assoc()): ?>
            <li>
                <?php echo $row['event_date']; ?> - 
                <strong><?php echo htmlspecialchars($row['event_text']); ?></strong> 
                (Priority: <?php echo $row['importance'] === '3' ? "High" : ($row['importance'] === '2' ? "Medium" : "Low"); ?>)
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>