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
        window.onload = function(){
            var canvas = document.getElementById('canvas');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            var ctx = canvas.getContext('2d'),
                w = canvas.width,
                h = canvas.height;


            hue = 217,
            stars = [],
            count = 0,
            maxStars = 1400;
            // Cache gradient
            var canvas2 = document.createElement('canvas'),
                ctx2 = canvas2.getContext('2d');
            canvas2.width = 100;
            canvas2.height = 100;
            var half = canvas2.width / 2,
                gradient2 = ctx2.createRadialGradient(half, half, 0, half, half, half);
            gradient2.addColorStop(0.025, '#fff');
            gradient2.addColorStop(0.1, 'hsl(' + hue + ', 61%, 33%)');
            gradient2.addColorStop(0.25, 'hsl(' + hue + ', 64%, 6%)');
            gradient2.addColorStop(1, 'transparent');

            ctx2.fillStyle = gradient2;
            ctx2.beginPath();
            ctx2.arc(half, half, half, 0, Math.PI * 2);
            ctx2.fill();

            function random(min, max) {
                if (arguments.length < 2) {
                    max = min;
                    min = 0;
                }

                if (min > max) {
                    var hold = max;
                    max = min;
                    min = hold;
                }

                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            function maxOrbit(x, y) {
                var max = Math.max(x, y),
                    diameter = Math.round(Math.sqrt(max * max + max * max));
                return diameter / 2;
            }

            var Star = function() {

                this.orbitRadius = random(maxOrbit(w, h));
                this.radius = random(60, this.orbitRadius) / 12;
                this.orbitX = w / 2;
                this.orbitY = h / 2;
                this.timePassed = random(0, maxStars);
                this.speed = random(this.orbitRadius) / 50000;
                this.alpha = random(2, 10) / 10;

                count++;
                stars[count] = this;
            }

            Star.prototype.draw = function() {
                var x = Math.sin(this.timePassed) * this.orbitRadius + this.orbitX,
                    y = Math.cos(this.timePassed) * this.orbitRadius + this.orbitY,
                    twinkle = random(10);

                if (twinkle === 1 && this.alpha > 0) {
                    this.alpha -= 0.05;
                } else if (twinkle === 2 && this.alpha < 1) {
                    this.alpha += 0.05;
                }

                ctx.globalAlpha = this.alpha;
                ctx.drawImage(canvas2, x - this.radius / 2, y - this.radius / 2, this.radius, this.radius);
                this.timePassed += this.speed;
            }

            for (var i = 0; i < maxStars; i++) {
                new Star();
            }

            function animation() {
                ctx.globalCompositeOperation = 'source-over';
                ctx.globalAlpha = 0.8;
                ctx.fillStyle = 'hsla(' + hue + ', 64%, 6%, 1)';
                ctx.fillRect(0, 0, w, h)

                ctx.globalCompositeOperation = 'lighter';
                for (var i = 1, l = stars.length; i < l; i++) {
                    stars[i].draw();
                };

                window.requestAnimationFrame(animation);
            }

            animation();
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


