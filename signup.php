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
        <link rel="stylesheet" href="loginStyles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <body>
    <!-- registration form section -->
    <section>
        <div class="registration-container">
            <h2>Starfinder Registration</h2>

            <!-- Error message display area -->
            <p id="error_message" style="color:red;"></p>

            <form onsubmit="submitForm(event)">
                Username: 
                <div class="input-box">
                    <span class="icon">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input required type="text" name="username" id="username">
                </div>

                Password: 
                <div class="input-box">
                    <span class="icon">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input required type="password" name="password" id="password">
                </div>

                Email: 
                <div class="input-box">
                    <span class="icon">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input required type="email" name="email" id="email">
                </div>

                Phone Number: 
                <div class="input-box">
                    <span class="icon">
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    <input required type="text" name="phone" id="phone">
                </div>

                <button type="submit">Register</button>
            </form>

            <div class="create-account">
            <button onclick="window.location.href = 'index.php';">Back</button>
            </div>
        </div>
    </section>
</body>
</html>
