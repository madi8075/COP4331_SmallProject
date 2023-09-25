<?php
    // Include the database connection file
    require 'connection.php';

    // Error Reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Start the session to access session variables
    session_start();

    $error_message = "";  // Initialize error message

    // Check if 'user_id' is set in the session
    if(!isset($_SESSION['user_id'])){
        die("User not logged in"); // Or redirect to login page
    }

    // Check if both 'name' and 'email' are set in the POST request, indicating the form has been submitted
    if(isset($_POST['name']) && isset($_POST['email'])) {

        // Retrieve the user ID from the session
        $user_id = $_SESSION['user_id'];

        // Retrieve name and email from the submitted form
        $name = $_POST['name'];
        $email = $_POST['email'];

        // Check if the combination of email and userID already exists in the database
        $checkEmailAndUserID = "SELECT * FROM contacts WHERE email = ? AND userID = ?";
        $stmt = $conn->prepare($checkEmailAndUserID);
        $stmt->bind_param("ss", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $stmt->close(); // Close statement after executing

        if($result->num_rows > 0) {
            // If the combination of email and userID exists, set an error message
            $error_message = "Email already associated with this user!";
        } else {
            // Prepare an SQL statement to insert the new contact into the database
            $stmt = $conn->prepare("INSERT INTO contacts (username, email, userID) VALUES (?, ?, ?)");

            // Bind the parameters to the SQL statement
            $stmt->bind_param("sss", $name, $email, $user_id);

            // Execute the SQL statement
            $stmt->execute();

            // Close the prepared statement
            $stmt->close();

            // Redirect the user to the 'contacts.php' page after successfully adding the contact
            header('Location: contacts.php');
            exit;
        }
    }
?>

<!-- Display the error message if it exists -->
<?php if($error_message): ?>
    <p style="color:red;"><?php echo $error_message; ?></p>
<?php endif; ?>

<!-- Contact addition form -->
<form method="post" action="addcontacts.php">
    <!-- Input field for name -->
    Name: <input type="text" name="name" required><br>
    <!-- Input field for email -->
    Email: <input type="email" name="email" required><br>
    <!-- Submit button for the form -->
    <input type="submit" value="Add Contact">
</form>

<!-- A link to navigate back to the 'contacts.php' page -->
<a href="contacts.php" class="button">Back to Contacts</a>
