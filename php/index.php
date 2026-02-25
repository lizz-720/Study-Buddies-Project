<?php
    session_start();

    if(!isset($_SESSION['user_id']))
        {
            header("location:login.php");
            exit();
        }
        

    $host = 'db';
    $user = 'root';
    $password = 'root_password';
    $db = 'studyguide_db';

    $conn = new mysqli($host, $user, $password, $db);

    if($conn->connect_error) 
        {
            die("connection failed: " . $conn->connect_error);
        }
    
        // Handle adding a new subject from the form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_subject'])) {
        $user_id = $_SESSION['user_id'];
        $subject_name = $conn->real_escape_string($_POST['new_subject']);
        
        // Match the columns in your setup.sql (user_id, subject_name, difficulty)
        $sql = "INSERT INTO subjects (user_id, subject_name, difficulty) VALUES ('$user_id', '$subject_name', 'Medium')";
        
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
        <title>Study Guide Creator</title>
        <style>
            body { font-family: sans-serif; max-width: 800px; margin: 2em auto; line-height: 1.6; }
            .result-box { background: #f4f4f4; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <a href="logout.php"><button>Logout</button></a>
        <h1>Study Guide Generator</h1>
        
        <!--<a href="?add=true"><button>Add Sample Subject (Math)</button></a> -->
        <form method = "POST" style = "margin: 20px 0;">
            <input type = "text" name = "new_subject" placeholder = "Enter new subject name" required>
            <button type = "submit"> Add Subject</button>
        </form>
        <h3 style="margin-top: 30px;">Database Records</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Subject</th>
                <!--<th>Difficulty</th> -->
            </tr>
            <?php
            $current_user = $_SESSION['user_id'];
            $result = $conn->query("SELECT id, subject_name FROM subjects WHERE user_id = '$current_user'");
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["id"] . "</td>
                            <td>" . htmlspecialchars($row["subject_name"]) . "</td>
                           
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No data found. Click the button above!</td></tr>";
            }
            ?>
    </body>
</html>