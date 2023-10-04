<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); // Set content type to JSON
    
    $response = array(); // Array to store the response

    // Include the database connection file
    require 'connection.php';

    // Check if the database connection is established properly
    if ($conn->connect_error) {
        $response["status"] = "error";
        $response["message"] = "Connection failed: " . $conn->connect_error;
        echo json_encode($response);
        exit;
    }

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // Check for JSON errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response["status"] = "error";
        $response["message"] = "Invalid JSON data received.";
        echo json_encode($response);
        exit;
    }

    if(isset($data['username']) && isset($data['password']) && isset($data['email']) && isset($data['phone'])) {
        $username = mysqli_real_escape_string($conn, $data['username']);
        $password = mysqli_real_escape_string($conn, $data['password']);
        $email = mysqli_real_escape_string($conn, $data['email']);
        $phone = mysqli_real_escape_string($conn, $data['phone']);

        // SQL query to check if the entered username already exists
        $checkUsername = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $checkUsername);

        if(mysqli_num_rows($result) > 0) {
            $response["status"] = "error";
            $response["message"] = "Username already exists!";
        } else {
            $sql = "INSERT INTO users (username, password, email, phone) VALUES ('$username','$password','$email','$phone')";
            if(mysqli_query($conn, $sql)) {
                $response["status"] = "success";
                $response["message"] = "User registered successfully!";
            } else {
                $response["status"] = "error";
                $response["message"] = "Error registering user!";
            }
        }
        echo json_encode($response);
        exit;
    } else {
        $response["status"] = "error";
        $response["message"] = "Required fields missing!";
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register</title>
        <script>
            async function submitForm(event) {
                event.preventDefault();

                // Capture the form data
                let formData = {
                    username: document.getElementById("username").value,
                    password: document.getElementById("password").value,
                    email: document.getElementById("email").value,
                    phone: document.getElementById("phone").value
                };

                // Send the data as JSON to signup.php
                try {
                    let response = await fetch('signup.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    let data = await response.json();

                    if (data.status === 'error') {
                        document.getElementById("error_message").innerText = data.message;
                    }else{
                        window.location.href = "index.php";  // Redirect to login page
                    }
                } catch (error) {
                    console.error("Error:", error);
                }
            }
        </script>
       
    </head>

    <body>
        <!-- Error message display area -->
        <p id="error_message" style="color:red;"></p>

        <!-- Registration form -->
        <form onsubmit="submitForm(event)">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" required><br><br>

            <input type="submit" value="Register">
        </form>

        <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #007BFF; color: #FFF; text-decoration: none; border-radius: 5px;">Go Back</a>
    </body>
</html>
