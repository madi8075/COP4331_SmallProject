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

            // Function to create a starry background animation.
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

                // Utility function to generate random numbers within a range.
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

                // Function to calculate the maximum orbit for a star.
                function maxOrbit(x, y) {
                    var max = Math.max(x, y),
                        diameter = Math.round(Math.sqrt(max * max + max * max));
                    return diameter / 2;
                }

                // Star object constructor.
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

                // Draw method for the Star object.
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

                // Create the initial set of stars.
                for (var i = 0; i < maxStars; i++) {
                    new Star();
                }

                // Animation function to update the starry background.
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

                // Start the animation.
                animation();
            }
        </script>
        <style>
            // Style for the canvas to make it cover the entire viewport and sit behind other content.
            #canvas {
                position: absolute;
                top: 0;
                left: 0;
                z-index: -1;
            }
        </style>
        <!-- Link to the external stylesheet for the page. -->
        <link rel="stylesheet" href="addContactsStyles.css">
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
