<?php

// Include the database connection file
require 'connection.php';

// Check if user requested to sign out
if (isset($_GET['signout']) && $_GET['signout'] == 'true') {
    // Clear session variables
    $_SESSION = array();
    // Destroy the session
    session_destroy();
    // Redirect user to index.php
    header('Location: index.php');
    exit;
}

// Start the session
session_start();

// If name and email are set in the POST request, this block will run
if(isset($_POST['name']) && isset($_POST['email'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    // Prepare the SQL statement to insert the new contact
    $stmt = $conn->prepare("INSERT INTO contacts (username, email, userID) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $user_id);
    $stmt->execute();
    $stmt->close();
    // Redirect to contacts.php after insertion
    header('Location: contacts.php');
    exit;
}

// Fetch contacts of the user
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM contacts WHERE userID = '$user_id'");

// If a contact id is set in the GET request, delete the corresponding contact
if(isset($_GET['id'])) {
    $contact_id = $_GET['id'];
    $conn->query("DELETE FROM contacts WHERE id = '$contact_id'");
    header('Location: contacts.php');
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Starfinder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include external CSS stylesheet -->
    <link rel="stylesheet" href="styles.css">
    <!-- Link to favicon for the website -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
</head>

<body>
    <!-- Background video -->
    <video autoplay muted loop plays-inline class="background-video">
        <source src="images/nebula.mp4" type="video/mp4">
    </video>

    <div class="main">
        <!-- Sign out link -->
        <a href="contacts.php?signout=true" class="glow-on-hover" style="right: 10px; top: 5px;">Sign Out</a>
        <!-- Contacts title -->
        <h1 class="floating glowing title">Contacts</h1>
        <!-- Add new contact link -->
        <a href="addcontacts.php" style="left:10px; top: 5px;" class="glow-on-hover">Add New Contact</a>
        <!-- Table for displaying contacts -->
        <div class="box">
            <table class="contact-table">
                <!-- Table header -->
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>View</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>

                <?php
                // Fetch and display contacts in table rows

                $user_id = $_SESSION['user_id'];
                $result = $conn->query("SELECT * FROM contacts WHERE userID = '$user_id'");

                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";  // Display the name
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";    // Display the email
                    echo "<td><button class='viewButton'><img src='images/magGlass.png' class='magGlass'></button></td>";
                    echo "<td><button class='viewButton'><img src='images/pencil1.png' class='magGlass'></button></td>";
                    // Link for deleting the contact
                    echo "<td><a href='contacts.php?id=" . $row['id'] . "'><button class='viewButton'><img src='images/trash.gif' class='magGlass'></button></a></td>";
                    echo "</tr>";
                }
                ?>

            </table>
        </div>
    </div>
</body>

</html>
