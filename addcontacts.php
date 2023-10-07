<?php
    ob_start();  // Starts output buffering to prevent unwanted output before headers.

    // Check if the request method is POST.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Connect to the database.
        require 'connection.php';
        
        // Start a session.
        session_start();
        
        // Set the content type of the response to JSON.
        header('Content-Type: application/json');

        $response = array(); // Array to store response data.

        // Check if the user is logged in.
        if (!isset($_SESSION['user_id'])) {
            $response["status"] = "error";
            $response["message"] = "User not logged in";
            echo json_encode($response);
            exit;
        }

        // Get the JSON payload from the request.
        $data = json_decode(file_get_contents('php://input'), true);

        // Check if necessary data exists in the payload.
        if (isset($data['name']) && isset($data['email']) && isset($data['phone'])) {
            $user_id = $_SESSION['user_id'];
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'];

            // Query to check if email is already associated with this user.
            $checkEmailAndUserID = "SELECT * FROM contacts WHERE email = ? AND userID = ?";
            $stmt = $conn->prepare($checkEmailAndUserID);
            $stmt->bind_param("ss", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                // If the email is already associated, return an error.
                $response["status"] = "error";
                $response["message"] = "Email already associated with this user!";
            } else {
                // Otherwise, insert the new contact into the database.
                $stmt = $conn->prepare("INSERT INTO contacts (username, email, phone, userID) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $phone, $user_id);
                $stmt->execute();
                $stmt->close();
                $response["status"] = "success";
                $response["message"] = "Contact added successfully!";
            }
            // Echo the response as JSON and exit the script.
            echo json_encode($response);
            exit;
        }
    }

    ob_end_clean();  // End output buffering and discard any captured output.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Contact</title>
    <script>
        // JavaScript that runs when the document is loaded.
        document.addEventListener("DOMContentLoaded", function() {
            // Attach an event listener to the form submission.
            document.getElementById("addContactForm").addEventListener("submit", async function(e) {
                e.preventDefault();

                // Retrieve form data.
                let name = document.getElementById("name").value;
                let email = document.getElementById("email").value;
                let phone = document.getElementById("phone").value;

                // Make an asynchronous POST request to add the contact.
                let response = await fetch('addcontacts.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ name: name, email: email, phone: phone })
                });

                let data = await response.json();

                // Handle the response from the server.
                if (data.status === 'success') {
                    // If successful, redirect to the contacts page.
                    window.location.href = 'contacts.php';
                } else {
                    // If there's an error, display it.
                    document.getElementById("error_message").innerText = data.message;
                }
            });
        });
        window.onload = function() {
            const bgCanvas = document.getElementById('canvas');
            bgCanvas.width = window.innerWidth;
            bgCanvas.height = window.innerHeight;
            const sky = bgCanvas.getContext('2d'),
                width = bgCanvas.width,
                height = bgCanvas.height;

            const mainHue = 217,
                celestialObjects = [],
                maxObjects = 1400;

            const tempCanvas = document.createElement('canvas'),
                tempCtx = tempCanvas.getContext('2d');
            tempCanvas.width = 100;
            tempCanvas.height = 100;
            const middle = tempCanvas.width / 2,
                radialGradient = tempCtx.createRadialGradient(middle, middle, 0, middle, middle, middle);
            radialGradient.addColorStop(0.025, '#fff');
            radialGradient.addColorStop(0.1, 'hsl(' + mainHue + ', 61%, 33%)');
            radialGradient.addColorStop(0.25, 'hsl(' + mainHue + ', 64%, 6%)');
            radialGradient.addColorStop(1, 'transparent');

            tempCtx.fillStyle = radialGradient;
            tempCtx.beginPath();
            tempCtx.arc(middle, middle, middle, 0, Math.PI * 2);
            tempCtx.fill();

            function randomValue(min, max) {
                if (arguments.length < 2) {
                    max = min;
                    min = 0;
                }
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            function calculateOrbit(x, y) {
                return Math.round(Math.sqrt(x * x + y * y)) / 2;
            }

            class Celestial {
                constructor() {
                    this.orbit = randomValue(calculateOrbit(width, height));
                    this.size = randomValue(60, this.orbit) / 12;
                    this.centerX = width / 2;
                    this.centerY = height / 2;
                    this.passedTime = randomValue(0, maxObjects);
                    this.movementSpeed = randomValue(this.orbit) / 50000;
                    this.transparency = randomValue(2, 10) / 10;

                    celestialObjects.push(this);
                }

                render() {
                    const posX = Math.sin(this.passedTime) * this.orbit + this.centerX,
                        posY = Math.cos(this.passedTime) * this.orbit + this.centerY;
                    const sparkle = randomValue(10);

                    if (sparkle === 1 && this.transparency > 0) {
                        this.transparency -= 0.05;
                    } else if (sparkle === 2 && this.transparency < 1) {
                        this.transparency += 0.05;
                    }

                    sky.globalAlpha = this.transparency;
                    sky.drawImage(tempCanvas, posX - this.size / 2, posY - this.size / 2, this.size, this.size);
                    this.passedTime += this.movementSpeed;
                }
            }

            for (let i = 0; i < maxObjects; i++) {
                new Celestial();
            }

            function animateSky() {
                sky.globalCompositeOperation = 'source-over';
                sky.globalAlpha = 0.8;
                sky.fillStyle = 'hsla(' + mainHue + ', 64%, 6%, 1)';
                sky.fillRect(0, 0, width, height);
                sky.globalCompositeOperation = 'lighter';

                for (const celestial of celestialObjects) {
                    celestial.render();
                }

                window.requestAnimationFrame(animateSky);
            }

            animateSky();
        }
    </script>
    <style>
        #canvas {
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
        }
    </style>
    <link rel="stylesheet" href="addContactsStyles.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
</head>

<body>
    <canvas id="canvas"></canvas>
    <!-- Display any error messages here. -->
    <p id="error_message" style="color:red;"></p>

    <!-- Form to add a contact. -->
    <<body>
    <canvas id="canvas"></canvas>
    <!-- Display any error messages here. -->
    <p id="error_message" style="color:red;"></p>

    <!-- Form to add a contact. -->
    <div class="box">
        <h2>Add Contact</h2>
        <form id="addContactForm">
            <div class="inputBox">
                <input type="text" id="name" required>
                <label for="name" style="color:white">Name</label>
            </div>
            <div class="inputBox">
                <input type="email" id="email" required>
                <label for="email" style="color:white">Email</label>
            </div>
            <div class="inputBox">
                <input type="tel" id="phone" required>
                <label for="phone" style="color:white">Phone</label>
            </div>
            <input type="submit" value="Add Contact">
            <!-- Link to navigate back to the contacts page. -->
            <a href="contacts.php" class="button" style="display: block; margin-top: 20px; color: white;">Back to Contacts</a>
        </form>
    </div>
</body>
</html>