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
    </script>
    <style>
            body {
                margin: 0;
                height: 100vh; /* make the body take the full viewport height */
                display: flex;
                justify-content: center; /* center horizontally */
                align-items: center; /* center vertically */
                flex-direction: column; /* stack children vertically */
            }

            form {
                display: flex;
                flex-direction: column;
                gap: 10px; /* space between form elements */
                width: 300px; /* or whatever width you prefer */
            }

            a {
                margin-top: 20px;
            }
    </style>
</head>

<body>

    <!-- Display any error messages here. -->
    <p id="error_message" style="color:red;"></p>

    <!-- Form to add a contact. -->
    <form id="addContactForm">
        Name: <input type="text" id="name" required><br>
        Email: <input type="email" id="email" required><br>
        Phone: <input type="tel" id="phone" required><br>
        <input type="submit" value="Add Contact">
    </form>

    <!-- Link to navigate back to the contacts page. -->
    <a href="contacts.php" class="button">Back to Contacts</a>

</body>
</html>
