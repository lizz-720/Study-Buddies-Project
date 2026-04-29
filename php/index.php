<?php
    session_start();

    // 1. SECURITY CHECK: Always first[cite: 1, 4]
    if(!isset($_SESSION['user_id'])) {
        header("location:login.php");
        exit();
    }

    // 2. CONNECTION: Establish your database link[cite: 1, 4]
    $host = 'db';
    $user = 'root';
    $password = 'root_password';
    $db = 'studyguide_db';
    $conn = new mysqli($host, $user, $password, $db);

    if($conn->connect_error) {
        die("connection failed: " . $conn->connect_error);
    }

    // 3. VARIABLE DEFINITIONS: Fixes the 'Undefined' errors[cite: 1, 4]
    $current_user = $_SESSION['user_id'];

    // 4. DATA FETCHING: Get your calendar dots and subject list[cite: 1, 4]
    $events = [];
    $event_query = "SELECT event_text, event_date, importance FROM calendar_events WHERE user_id = '$current_user'";
    $event_result = $conn->query($event_query);
    if ($event_result) {
        while($row = $event_result->fetch_assoc()) {
            $events[$row['event_date']][] = $row;
        }
    }

    // 5. FORM HANDLING: Process "Add Subject" or "Delete" requests[cite: 1, 4]
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_subject'])) {
        $subject_name = $conn->real_escape_string($_POST['new_subject']);
        $sql = "INSERT INTO subjects (user_id, subject_name, difficulty) VALUES ('$current_user', '$subject_name', 'Medium')";
        if ($conn->query($sql)) {
            header("Location: index.php");
            exit();
        }
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['topic'])) 
        {
            $topic = urlencode($_POST['topic']);
            
            // 1. THE HEAVY LIFTING CALL
            // Note: 'logic-api' is the service name from your compose.yaml
            $python_url = "http://logic-api:8000/generate-guide/$topic";
            
            // We use file_get_contents for a simple GET request to Python
            $response = file_get_contents($python_url);
            
            if ($response) {
                $data = json_decode($response, true);
                echo "<h3>Result for: " . htmlspecialchars($_POST['topic']) . "</h3>";
                echo "<div class='result-box'>" . $data['guide'] . "</div>";
            } else {
                echo "<p style='color:red;'>Error: Could not reach the Python logic-api.</p>";
            }
        }
?>
<!DOCTYPE html>    
<html lang = "en">
    <head>
        <meta charset = "UTF-8">
        <title>Study Guide Creator</title>
        <link rel = "stylesheet" href = "CSS/index.css">
    </head>
    <body>
            <nav class="top-bar">
                <div class="nav-left"></div> <!-- Spacer to keep title centered -->
                <div class="nav-center">
                    <h1>Study Buddies</h1>
                </div>
                <div class="nav-right">
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </nav>
        <div class="dashboard">
            <!-- TOP LEFT: Navigation Tools -->
            <div class="section-box">
                <h3>🚀 Study Tools</h3>
                <div class="tool-grid">
                    <a href="flashcards.php" class="tool-btn">Flashcards</a>
                    <a href="timer.php" class="tool-btn">Study Timer</a>
                    <a href="fill_blank.php" class="tool-btn">Fill in the Blank</a>
                    <a href="multiplechoice.php" class="tool-btn">Multiple Choice</a>
                </div>
            </div>

<!-- TOP RIGHT: Calendar View -->
<div class="section-box" style="position: relative;">
    <div class="calendar-header">
        <!-- Dynamic Month and Year Display -->
        <h3>📅 <?php echo date('F Y'); ?></h3>
        <a href="add_event.php" class="add-btn">+</a>
    </div>
    <div class="calendar-grid">
        <?php
        $daysInMonth = date('t');
        $currentMonthYear = date('Y-m-');

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = $currentMonthYear . sprintf("%02d", $i);
            echo "<div class='day-cell'>$i";
            
            if (isset($events[$dateStr])) {
                echo "<br>";
                foreach ($events[$dateStr] as $e) {
                    $dotClass = ($e['importance'] == 2) ? 'dot-quiz' : 'dot-project';
                    echo "<span class='dot $dotClass' title='".htmlspecialchars($e['event_text'])."'></span>";
                }
            }
            echo "</div>";
        }
        ?>
    </div>
</div>

            <!-- BOTTOM LEFT: Subject Management -->
            <div class="section-box">
                <h3>📚 My Subjects</h3>
                <form method="POST" style="margin-bottom: 15px;">
                    <input type="text" name="new_subject" placeholder="New subject..." required style="width: 70%;">
                    <button type="submit">Add</button>
                </form>
                <table>
                    <tr><th>Subject</th><th>Action</th></tr>
                    <?php
                    $result = $conn->query("SELECT id, subject_name FROM subjects WHERE user_id = '$current_user'");
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>".htmlspecialchars($row['subject_name'])."</td>
                                <td>
                                    <a href='flashcards.php?subject_id=".$row['id']."'>Open</a> | 
                                    <a href='delete_subject.php?id=".$row['id']."' class='remove-link' onclick='return confirm(\"Delete this subject?\")'>Remove</a>
                                </td>
                            </tr>";
                    }
                    ?>
                </table>
            </div>

            <!-- BOTTOM RIGHT: Upcoming Deadlines -->
            <div class="section-box">
            <h3>🔔 Upcoming Deadlines</h3>
            <div class="list-view">
                <ul style="list-style: none; padding: 0;">
                    <?php
                    if (!empty($events)) {
                        foreach ($events as $date => $dayEvents) {
                            foreach ($dayEvents as $e) {
                                // Use importance for color: 2 is Red (Quiz), 1 is Green (Project)
                                $color = ($e['importance'] == 2) ? '#ff4757' : '#2ed573';
                                echo "<li style='border-left: 4px solid $color; padding-left: 10px; margin-bottom: 10px; font-family: sans-serif;'>
                                        <strong>$date</strong>: " . htmlspecialchars($e['event_text']) . "
                                    </li>";
                            }
                        }
                    } else {
                        echo "<li>Relax! No upcoming deadlines.</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </body>
</html>