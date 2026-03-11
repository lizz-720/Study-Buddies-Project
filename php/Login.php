<?php
session_start();

$host = 'db';
$user = 'root';
$password = 'root_password';
$db = 'studyguide_db';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// handle the login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $conn->real_escape_string($_POST['Username']);
    $pass = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = '$pass'");

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['name'] = $user_data['name'];

        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset = "UTF-8">
    <title>Study Buddies Login</title>
    <link rel = "stylesheet" href = "CSS/Login.css">
</head>
<body>
<div class = "login-container">
    <h1>Study Buddies Login</h1>

        <?php
        if (isset($error)) {
            echo "<p style='color:red;'>$error</p>";
        }
        ?>

        <form method="POST">
            <input type="text" name="Username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>

        <hr>

        <p>Don't have an account?</p>
        <form action="registration.php">
            <button type="submit">New User</button>
        </form>

    </div>
</body>
</html>