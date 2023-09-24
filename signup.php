<?php
// Include the database connection file
require 'connection.php';

// Initialize the error message to an empty string
$error_message = "";

// Check if the database connection is established properly
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // End the script if there's a connection error
}

// Check if the request method is POST, which implies that the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escaping the strings to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // SQL query to check if the entered username already exists in the database
    $checkUsername = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $checkUsername);

    // Check if the number of rows (matching users) is greater than 0
    if(mysqli_num_rows($result) > 0) {
        // If the username already exists, set the error message
        $error_message = "Username already exists!";
    } else {
        // If the username is unique, prepare the SQL query to insert the new user
        $sql = "INSERT INTO users (username, password, email) VALUES ('$username','$password','$email')";

        // Execute the insertion query
        if(mysqli_query($conn, $sql)) {
            // If registration is successful, redirect the user to index.php
            header('Location: index.php');
            exit;
        } else {
            // If there's an error during registration, set the error message
            $error_message = "Error registering user!";
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <!-- If there's an error message, display it in red -->
    <?php if($error_message): ?>
        <p style="color:red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <!-- Registration form -->
    <form action="signup.php" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br><br>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required><br><br>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required><br><br>
        
        <input type="submit" value="Register">
    </form>
</body>
</html>
