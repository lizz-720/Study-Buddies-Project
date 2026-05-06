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

    // 4. FORM HANDLING: Process Add Event, Add Subject, or AI requests[cite: 6, 9]
    
    // NEW: Handle Calendar Event Addition[cite: 6]
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
        $e_date = $conn->real_escape_string($_POST['event_date']);
        $e_text = $conn->real_escape_string($_POST['event_text']);
        $e_importance = (int)$_POST['importance'];

        $conn->query("INSERT INTO calendar_events (user_id, event_date, event_text, importance) 
                      VALUES ('$current_user', '$e_date', '$e_text', '$e_importance')");
        header("Location: index.php");
        exit();
    }

    // Add Subject[cite: 9]
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_subject'])) {
        $subject_name = $conn->real_escape_string($_POST['new_subject']);
        $sql = "INSERT INTO subjects (user_id, subject_name, difficulty) VALUES ('$current_user', '$subject_name', 'Medium')";
        if ($conn->query($sql)) {
            header("Location: index.php");
            exit();
        }
    }

    // AI Guide Generation[cite: 9]
    if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['topic'])) {
        $topic = urlencode($_POST['topic']);
        $python_url = "http://logic-api:8000/generate-guide/$topic";
        $response = file_get_contents($python_url);
        
        if ($response) {
            $data = json_decode($response, true);
            $ai_result = "<div class='result-box'><h3>Result for: " . htmlspecialchars($_POST['topic']) . "</h3>" . $data['guide'] . "</div>";
        }
    }

    // 5. DATA FETCHING: Get your calendar dots after potential updates[cite: 1, 4]
    // 5. DATA FETCHING: Get your calendar dots after potential updates
    $events = [];
    // Added missing semicolon at the end of the line below
    $event_query = "SELECT id, event_text, event_date, importance FROM calendar_events WHERE user_id = '$current_user' ORDER BY event_date ASC"; 
    $event_result = $conn->query($event_query);
    if ($event_result) {
        while($row = $event_result->fetch_assoc()) {
            $events[$row['event_date']][] = $row;
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
        <style>
            /* Ensure the dots match your 4-color importance levels[cite: 8, 10] */
            .dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin: 1px; }
            .dot-2 { background-color: #ff4757; } /* Red: Quiz */
            .dot-1 { background-color: #2ed573; } /* Green: Project */
            .dot-3 { background-color: #FFBF00; } /* Yellow: Assignment */
            .dot-4 { background-color: #FF8DA1; } /* Pink: Other */
        </style>
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

<!-- CALENDAR LOGIC: Must come before the HTML that uses it[cite: 7] -->
<?php
    // 1. Get current month/year from URL, or default to "now"
    $month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
    $year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

    // 2. Calculate Previous Month/Year
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }

    // 3. Calculate Next Month/Year
    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }

    // 4. Create the timestamp and get month details
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth = date('t', $firstDayOfMonth);
    $monthName = date('F', $firstDayOfMonth);
    $dayOfWeek = date('w', $firstDayOfMonth); // Start day of week (0-6)
?>

<!-- TOP RIGHT: Calendar View[cite: 7] -->
<div class="section-box">
    <div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <a href="?m=<?= $prevMonth ?>&y=<?= $prevYear ?>" style="text-decoration: none; color: #436EEE; font-weight: bold;">&lt; Prev</a>
        
        <h3 style="margin: 0;">📅 <?= $monthName . " " . $year ?></h3>
        
        <a href="?m=<?= $nextMonth ?>&y=<?= $nextYear ?>" style="text-decoration: none; color: #436EEE; font-weight: bold;">Next &gt;</a>
    </div>

    <div class="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; background: #ccc; border: 1px solid #ccc;">
        <?php
            // 1. Fill in empty cells for the previous month's trailing days[cite: 7]
            for ($i = 0; $i < $dayOfWeek; $i++) {
                echo "<div class='day-cell' style='background: #eee; min-height: 50px;'></div>";
            }

            // 2. Fill in the actual days of the month[cite: 7]
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $i);
                echo "<div class='day-cell' style='background: white; min-height: 50px; padding: 5px; text-align: center;'>$i";
                
                // Inside your for loop for the days of the month[cite: 7]
                if (isset($events[$dateStr])) {
                    echo "<br>";
                    foreach ($events[$dateStr] as $e) {
                        // Change this line to use a dynamic class based on importance
                        // This will generate classes like dot-1, dot-2, dot-3, and dot-4
                        echo "<span class='dot dot-" . $e['importance'] . "' title='" . htmlspecialchars($e['event_text']) . "'></span>";
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
                    <?php
                    $result = $conn->query("SELECT id, subject_name FROM subjects WHERE user_id = '$current_user'");
                    while($row = $result->fetch_assoc()) {
                        echo "<tr><td>".htmlspecialchars($row['subject_name'])."</td><td><a href='flashcards.php?subject_id=".$row['id']."'>Open</a></td></tr>";
                    }
                    ?>
                </table>
            </div>

            <!-- BOTTOM RIGHT: Deadlines & Add Event[cite: 6] -->
            <div class="section-box">
                <h3>📅 Add Important Date</h3>
                <form method="POST" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                    <input type="text" name="event_text" placeholder="Event Name (e.g. CPSC 335 Quiz)" required style="padding: 5px;">
                    <input type="date" name="event_date" required style="padding: 5px;">
                    <select name="importance" style="padding: 5px;">
                        <option value="2">Red (Quiz)</option>
                        <option value="1">Green (Project)</option>
                        <option value="3">Yellow (Assignment)</option>
                        <option value="4">Pink (Other)</option>
                    </select>
                    <button type="submit" name="add_event">Add Event</button>
                </form>

                <h3>📌 Upcoming Deadlines</h3>
                <div class="list-view">
                    <ul style="list-style: none; padding: 0;">
                        <?php
                        if (!empty($events)) {
                            foreach ($events as $date => $dayEvents) {
                                foreach ($dayEvents as $e) {
                                    $color = '#ccc'; 
                                    if ($e['importance'] == 2) $color = '#ff4757'; // Red
                                    elseif ($e['importance'] == 1) $color = '#2ed573'; // Green
                                    elseif ($e['importance'] == 3) $color = '#FFBF00'; // Yellow
                                    elseif ($e['importance'] == 4) $color = '#FF8DA1'; // Pink
                                    // Ensure the semicolon above replaces the[cite: 10] placeholder in your snippet[cite: 10, 11]

                                    echo "<li style='border-left: 4px solid $color; padding-left: 10px; margin-bottom: 10px; display: flex; justify-content: space-between;'>
                                            <span><strong>$date</strong>: " . htmlspecialchars($e['event_text']) . "</span>
                                            <a href='delete_event.php?id=" . $e['id'] . "' 
                                            style='color: #ff4757; text-decoration: none; font-size: 0.8em;' 
                                            onclick='return confirm(\"Remove this date?\")'>[Remove]</a>
                                        </li>";
                                }
                            }
                        } 
                        else { 
                            echo "<li>No deadlines yet!</li>"; }
                        
                        ?>
                    </ul>
                </div>
                
            </div>
<<div class="utility-row">
        <div class="section-box utility-card">
            <div class="utility-content">
                <div class="clock-side">
                    <h3>🕒 Local Time</h3>
                    <div id="liveClock" class="large-display">00:00:00 AM</div>
                </div>
                <div class="timer-side">
                    <h3>⏱️ Study Timer</h3>
                    <div class="timer-controls">
                        <input id="barStudyMins" type="number" min="1" value="25">
                        <button id="barStartBtn">Start</button>
                        <button id="barResetBtn">Reset</button>
                    </div>
                    <div id="barTimeDisplay" class="large-display">25:00</div>
                </div>
            </div>
        </div>
    </body>
</html>