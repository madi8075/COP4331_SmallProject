<?php

session_start();
require 'connection.php';

$response = array();

// Check if it's a POST request with a JSON content type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $data = json_decode(file_get_contents("php://input"), true);  // Get JSON data from the request body

    if(isset($data['username']) && isset($data['password'])) {
        $username = $data['username'];
        $password = $data['password'];

        // Using prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();

        $result = $stmt->get_result();
        if($row = $result->fetch_assoc()) {
            // User exists and credentials are correct
            $_SESSION['user_id'] = $row['id'];
            
            // Respond with a success status and a redirect URL
            $response["status"] = "success";
            $response["redirect"] = "contacts.php";
        } else {
            // Invalid credentials
            $response["status"] = "error";
            $response["message"] = "Invalid username or password!";
        }
        $stmt->close();
    } else {
        // Missing username or password in JSON request
        $response["status"] = "error";
        $response["message"] = "Missing username or password!";
    }

    header("Content-Type: application/json");
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script>
        async function submitForm(event) {
            event.preventDefault();

            const username = document.querySelector('[name="username"]').value;
            const password = document.querySelector('[name="password"]').value;

            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (data.status === "success") {
                window.location.href = data.redirect; // Redirect to the provided URL if login is successful
            } else {
                alert(data.message); // Display error message if provided
            }
        }
    </script>
</head>
<body>
    <!-- login form -->
    <form onsubmit="submitForm(event)">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" value="Login">
    </form>

    <a href="signup.php" class="button">Signup</a>
</body>
</html>
