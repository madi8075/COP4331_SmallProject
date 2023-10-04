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
        // Listen for clicks on the page
    document.addEventListener('click', function(event) {
        // Check if the clicked element, or its ancestors, has the 'deleteButton' class
        if (event.target.closest('.deleteButton')) {
                // Fetch the data-id attribute value of the closest element with the 'deleteButton' class
                // This is likely the ID of the contact we want to delete
                const contactId = event.target.closest('.deleteButton').getAttribute('data-id');
                
                // Send a request to 'contacts.php' to delete the contact
                fetch('contacts.php', {
                    method: 'POST', // Specify that this is a POST request
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded', // Set content type header
                    },
                    body: 'id=' + contactId // Include the contact ID in the request body
                })
                .then(response => response.json()) // Convert the server response to a JSON object
                .then(data => {
                    // Check the 'success' value in the server response
                    if (data.success) {
                        // If the contact was successfully deleted, remove its row from the table
                        event.target.closest('tr').remove();
                        // Notify the user of the success
                        alert('Contact deleted successfully!');
                    } else {
                        // If there was an error, notify the user
                        alert('Failed to delete the contact.');
                    }
                });
            }
        });
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

        <!-- Background video -->
        <video autoplay muted loop plays-inline class="background-video">
            <source src="images/nebula.mp4" type="video/mp4">
        </video>

        <!-- Sign out link -->
        <button style="right: 0.5%; top: 1%;"class="glow-on-hover">
            <a href="contacts.php?signout=true" >Sign Out</a>
        </button>

        <button style="right: 15%; top: 1%;"class="glow-on-hover">
            <a href="addcontacts.php" >Add New Contact</a>
        </button>
        
        <form id="searchForm" style="position: absolute;left:1%; top: 5px;width:240px;">
        <input type="text" name="search" id="searchInput" placeholder="Search by name">
            <input type="submit" value="Search">
        </form>
            <!-- Contacts title -->
            <div class="main">
            <h1 class="floating glowing title">Contacts</h1>
            <!-- Add new contact link -->
            
            <!-- Table for displaying contacts -->
            <div class="box">
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
