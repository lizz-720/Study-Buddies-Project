<?php
    session_start();
    $host = 'db';
    $user = 'root';
    $password = 'root_password';
    $db = 'studyguide_db';

    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) 
        {
            die("Connection failed: " . $conn->connect_error);
        }

    // HANDLE THE LOGIN SUBMISSION
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Username'])) {
        $username = $conn->real_escape_string($_POST['Username']);
        $pass = $_POST['password'];

        // Check if user exists in the 'users' table created by setup.sql
        $result = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = '$pass'");

        if ($result && $result->num_rows > 0) 
            {
                $user_data = $result->fetch_assoc();
                $_SESSION['user_id'] = $user_data['id']; // Store their ID
                $_SESSION['name'] = $user_data['name'];
                header("Location: index.php"); // Send them to the generator
                exit();
        } else {
                $error = "Invalid Username or Password!";
        }
    }
?>

<!DOCTYPE html>
<html lang = "en">
    <head>
        <meta charset = "UTF-8">
        <title>Study Buddies</title>
        <link rel = "stylesheet" href = "CSS/Login.css">
    </head>
    <!--<header>
        Study Buddies Login
    </header>-->
    <body>
        <div class = "login-container">
            <h1>Study Buddies Login</h1>

            <!--Checks if there is username/password is correct if not then itll create a an error message-->
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($error)): ?>
                <p style="color: red; font-weight: bold; background: #ffe6e6; padding: 10px; border: 1px solid red; border-radius: 5px; width: 250px;">
                    <?php echo $error; ?>
                </p>
            <?php endif; ?>

            <!--where user input fields are along with submit button-->
            <form method = "POST" style = "margin: 20px 0;">
                <input type = "text" name = "Username" placeholder = "Username" required><br>
                <input type = "password" name = "password" placeholder = "Password" required><br>
                <button type = "submit"> Login </button><br>
            </form>

            <!--create a break between the user inputs and allowing new users to creaete an account-->
            <hr>
            <p>New User?</p>
            <!--sends user to registration.php-->
            <a href = "registration.php"><button type = "New User"> Create Account </button></a>
        </div>
    </body>
</html>