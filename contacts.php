<?php
    // Include the database connection file
    require 'connection.php';

    // Start the session
    session_start();

    // Check for sign out request
    if (isset($_GET['signout']) && $_GET['signout'] == 'true') {
        session_destroy(); // Destroy the session
        header('Location: index.php'); // Redirect to index.php
        exit; // Stop further execution
    }

    if (isset($_POST['id'])) {
        $contactId = $_POST['id'];
        
        // Use a prepared statement to avoid SQL injection
        $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->bind_param("i", $contactId);
        
        $success = $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }

    $contacts = []; // Empty array to store contacts

    // If a user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Fetch contacts of the user
        $result = $conn->query("SELECT * FROM contacts WHERE userID = '$user_id'");

        while ($row = $result->fetch_assoc()) {
            $contacts[] = [
                'id' => $row['id'],
                'name' => $row['username'],
                'email' => $row['email'],
                'phone' => $row['phone']
            ];
        }
    }

    // If it's an AJAX request, return the JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($contacts);
        exit;
    }
?>

<script>
    // Fetch the contacts using AJAX
    fetch('contacts.php', {
        headers: {
            "X-Requested-With": "XMLHttpRequest" // This header tells PHP it's an AJAX request
        }
    })
    .then(response => response.json())
    .then(contacts => {
        // Get the table body
        let tbody = document.querySelector('.contact-table tbody');
        
        // Loop through the contacts and append to the table
        contacts.forEach(contact => {
    let tr = document.createElement('tr');
    tr.innerHTML = `
        <tr>
        <td>${contact.name}</td>
        <td>${contact.email}</td>
        <td>${contact.phone}</td>
        <td><button class="viewButton"><img src="images/magGlass.png" class="magGlass"></button></td>
        <td><button class="viewButton"><img src="images/pencil1.png" class="magGlass"></button></td>
        <td><button class="deleteButton" data-id="${contact.id}"><img src="images/trash.gif" class="magGlass"></button></td>
        </tr>
    `;
    tbody.appendChild(tr);
});

    });

    document.addEventListener('click', function(event) {
    if (event.target.closest('.deleteButton')) {
        const contactId = event.target.closest('.deleteButton').getAttribute('data-id');
        
        fetch('contacts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + contactId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from the table
                event.target.closest('tr').remove();
                alert('Contact deleted successfully!');
            } else {
                alert('Failed to delete the contact.');
            }
        });
    }
});

</script>



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
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>View</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>

        </table>
        </div>
    </div>
</body>

</html>
