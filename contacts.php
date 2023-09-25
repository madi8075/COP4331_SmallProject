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

// Handle deletion
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
    $query = "SELECT * FROM contacts WHERE userID = '$user_id'";

    // If there is a search term in the GET request
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchTerm = $conn->real_escape_string($_GET['search']); // Secure the search term
        $query .= " AND username LIKE '%$searchTerm%'";
    }

    // Fetch contacts of the user based on the query
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $contacts[] = [
            'id' => $row['id'],
            'name' => $row['username'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'date' => $row['reg_date'] // Fetching the reg_date from the result
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
document.addEventListener('DOMContentLoaded', function() {
    // This function fetches the contacts based on the current search value
    function fetchContacts() {
        // Get the current value from the search input
        let searchValue = document.getElementById('searchInput').value;
        let fetchUrl = 'contacts.php';
        
        // If there's a search value, append it to the fetch URL
        if (searchValue) {
            fetchUrl += '?search=' + encodeURIComponent(searchValue);
        }
        
        // Make an AJAX request to fetch contacts
        fetch(fetchUrl, {
            headers: {
                "X-Requested-With": "XMLHttpRequest" // This header tells PHP it's an AJAX request
            }
        })
        .then(response => response.json()) // Convert the response to JSON
        .then(contacts => {
            // Clear the current table contents
            let tbody = document.querySelector('.contact-table tbody');
            tbody.innerHTML = '';
            
            // Loop through the returned contacts and add them to the table
            contacts.forEach(contact => {
                let tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${contact.name}</td>
                    <td>${contact.email}</td>
                    <td>${contact.phone}</td>
                    <td>${contact.date}</td> <!-- Displaying the date -->
                    <td><button class="viewButton"><img src="images/magGlass.png" class="magGlass"></button></td>
                    <td><button class="viewButton"><img src="images/pencil1.png" class="magGlass"></button></td>
                    <td><button class="deleteButton" data-id="${contact.id}"><img src="images/trash.gif" class="magGlass"></button></td>
                `;
                tbody.appendChild(tr);
            });

        });
    }

    // Prevent the search form from submitting and reloading the page, fetch contacts instead
    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault();
        fetchContacts();
    });

    // Whenever the search input value changes, fetch the contacts
    document.getElementById('searchInput').addEventListener('input', function() {
        fetchContacts();
    });

    // Listen for clicks on the page
    document.addEventListener('click', function(event) {
        // If the clicked element is a delete button, handle the delete operation
        if (event.target.closest('.deleteButton')) {
            // ... [This part remains unchanged]
        }
    });

    // Fetch contacts when the page first loads
    fetchContacts();
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
        <!-- Search Form -->
        <form id="searchForm" style="left:300px; top: 5px;">
        <input type="text" name="search" id="searchInput" placeholder="Search by username">
            <input type="submit" value="Search">
        </form>

        <!-- Background video -->
        <video autoplay muted loop plays-inline class="background-video">
            <source src="images/nebula.mp4" type="video/mp4">
        </video>

        <div class="main">
            <!-- Sign out link -->
            <a href="contacts.php?signout=true" class="glow-on-hover" style="right: 10px; top: 5px;">Sign Out</a>
            <a href="addcontacts.php" class="glow-on-hover" style="right: 10px; top: 5px;">Add New Contact</a>
            <!-- Contacts title -->
            <h1 class="floating glowing title">Contacts</h1>
            <!-- Add new contact link -->
            
            <!-- Table for displaying contacts -->
            <div class="contact-table box">
            <table class="contact-table">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Date</th> <!-- Header for the Date column -->
                    <th>View</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </table>
            </div>
        </div>
    </body>
</html>
