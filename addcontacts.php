<?php
    ob_start();  // Start output buffering at the beginning

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Include the database connection file
        require 'connection.php';
        
        // Start the session
        session_start();
        
        header('Content-Type: application/json');

        $response = array();

        if (!isset($_SESSION['user_id'])) {
            $response["status"] = "error";
            $response["message"] = "User not logged in";
            echo json_encode($response);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['name']) && isset($data['email']) && isset($data['phone'])) {
            $user_id = $_SESSION['user_id'];
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'];

            $checkEmailAndUserID = "SELECT * FROM contacts WHERE email = ? AND userID = ?";
            $stmt = $conn->prepare($checkEmailAndUserID);
            $stmt->bind_param("ss", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                $response["status"] = "error";
                $response["message"] = "Email already associated with this user!";
            } else {
                $stmt = $conn->prepare("INSERT INTO contacts (username, email, phone, userID) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $phone, $user_id);
                $stmt->execute();
                $stmt->close();
                $response["status"] = "success";
                $response["message"] = "Contact added successfully!";
            }
            echo json_encode($response);
            exit; // Make sure to exit after sending the response.
        }
    }

    ob_end_clean();  // End output buffering and discard any captured output
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Contact</title>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("addContactForm").addEventListener("submit", async function(e) {
                e.preventDefault();

                let name = document.getElementById("name").value;
                let email = document.getElementById("email").value;
                let phone = document.getElementById("phone").value;

                let response = await fetch('addcontacts.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ name: name, email: email, phone: phone })
                });

                let data = await response.json();

                if (data.status === 'success') {
                    window.location.href = 'contacts.php';
                } else {
                    document.getElementById("error_message").innerText = data.message;
                }
            });
        });
    </script>
</head>

<body>

    <!-- Display the error message container -->
    <p id="error_message" style="color:red;"></p>

    <!-- Contact addition form -->
    <form id="addContactForm">
        Name: <input type="text" id="name" required><br>
        Email: <input type="email" id="email" required><br>
        Phone: <input type="tel" id="phone" required><br>
        <input type="submit" value="Add Contact">
    </form>

    <a href="contacts.php" class="button">Back to Contacts</a>

</body>
</html>
