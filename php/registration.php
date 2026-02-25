<?php
    session_start();
    $host = 'db';
    $user = 'root';
    $password = 'root_password';
    $db = 'studyguide_db';

    $conn = new mysqli($host, $user, $password, $db);

    if($conn->connect_error)
        {
            die("connection failed: " . $conn->connect_error);
        }
    //sends a submission to the database
    if($_SERVER['REQUEST_METHOD'] === 'POST')   {
        //COLLECTS THE INPUT
        $name = $conn->real_escape_string($_POST['Name']);
        $username = $conn->real_escape_string($_POST['Username']);
        $password = $conn->real_escape_string($_POST['Password']);

        //insters into database
        $insert_sql = "INSERT INTO users (name, username, password) VALUES('$name', '$username', '$password')";

        if($conn->query($insert_sql) === TRUE)
            {
                header("Location: Login.php");
                exit();
            }else{
                echo "Eroor: " . $conn->error;
            }
    }
?>

<!DOCTYPE html>
<html lang = "en">
    <head>
        <!--Study Buddies New User Registration-->
        <link rel = "stylesheet" href = "CSS/Login.css">
    </head>
    <body>
        <div class = "login-container">
            <h1>Study Buddies Registration</h1>
            <form method = "POST" style = "margin: 20px 0;">
                <input type = "text" name = "Name" placeholder = "Name" required><br>
                <input type = "text" name = "Username" placeholder = "Username" required><br>
                <input type = "text" name = "Password" placeholder = "Password" required><br>
                <button type = "submit"> Submit Registration</button>
            </form>
        <p>Already have an account>? <a href = "Login.php">Login here</a></p>
        </div>
    </body>
</html>