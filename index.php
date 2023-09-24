<?php

    session_start();

    require 'connection.php';

    // Assuming you've already set up a connection to the database and it's in the $conn variable
    if(isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Using prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();

        $result = $stmt->get_result();
        if($row = $result->fetch_assoc()) {
            // User exists and credentials are correct
            $_SESSION['user_id'] = $row['id'];
            header("Location: contacts.php");  // Redirect to contacts.php
            exit;
        } else {
            // Invalid credentials
            echo "Invalid username or password!";
        }
        $stmt->close();
    }
?>

<!-- login form -->
<form method="post" action="">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Login">
</form>

<a href="signup.php" class="button">Signup</a>
