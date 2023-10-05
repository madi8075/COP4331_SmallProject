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

    // Default to page 1 if no page is specified
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $contactsPerPage = 15;
    $offset = ($page - 1) * $contactsPerPage;

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

    // Handle editing
    if (isset($_POST['editId'])) {
        $editId = $_POST['editId'];
        $editName = $_POST['editName'];
        $editEmail = $_POST['editEmail'];
        $editPhone = $_POST['editPhone'];

        // Use a prepared statement to avoid SQL injection
        $stmt = $conn->prepare("UPDATE contacts SET username=?, email=?, phone=? WHERE id=?");
        $stmt->bind_param("sssi", $editName, $editEmail, $editPhone, $editId);
        
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

        // Add LIMIT and OFFSET clauses to the query
        $query .= " LIMIT $contactsPerPage OFFSET $offset";

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
// Wait for the document to be fully loaded before executing the script
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the current page to 1
    let currentPage = 1;

    // Function to fetch contacts based on the current page and search value
    function fetchContacts() {
        // Get the current value of the search input
        let searchValue = document.getElementById('searchInput').value;
        // Construct the URL for fetching contacts
        let fetchUrl = `contacts.php?page=${currentPage}`;
        
        // If there's a search value, append it to the URL
        if (searchValue) {
            fetchUrl += '&search=' + encodeURIComponent(searchValue);
        }
        
        // Fetch the contacts using the constructed URL
        fetch(fetchUrl, {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(contacts => {
            // Get the table body element
            let tbody = document.querySelector('.contact-table tbody');
            // Clear the current rows in the table body
            tbody.innerHTML = '';
            
            // Loop through the fetched contacts and add them to the table
            contacts.forEach(contact => {
                let tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${contact.name}</td>
                    <td>${contact.email}</td>
                    <td>${contact.phone}</td>
                    <td>${contact.date}</td>
                    <td><button class="editButton">Edit</button></td>
                    <td><button class="deleteButton" data-id="${contact.id}">Delete</button></td>
                `;
                tbody.appendChild(tr);
            });

            // Update the displayed current page number
            document.getElementById('currentPage').textContent = currentPage;
        });
    }

    // Add an event listener to the search form to fetch contacts when submitted
    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault();
        fetchContacts();
    });

    // Add an event listener to the search input to fetch contacts when its value changes
    document.getElementById('searchInput').addEventListener('input', function() {
        fetchContacts();
    });

    // Add an event listener to the entire document to handle edit button clicks
    document.addEventListener('click', function(event) {
        if (event.target.closest('.editButton')) {
            // Get the contact details from the clicked row
            const contactId = event.target.closest('tr').querySelector('.deleteButton').getAttribute('data-id');
            const contactName = event.target.closest('tr').querySelector('td:nth-child(1)').textContent;
            const contactEmail = event.target.closest('tr').querySelector('td:nth-child(2)').textContent;
            const contactPhone = event.target.closest('tr').querySelector('td:nth-child(3)').textContent;

            // Set the values in the edit form
            document.getElementById('editId').value = contactId;
            document.getElementById('editName').value = contactName;
            document.getElementById('editEmail').value = contactEmail;
            document.getElementById('editPhone').value = contactPhone;

            // Display the edit modal
            document.getElementById('editModal').style.display = 'block';
        }
    });

    // Add an event listener to the edit form to handle its submission
    document.getElementById('editForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Get the form data
        const formData = new FormData(event.target);
        // Send the form data to the server
        fetch('contacts.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Check the server's response and display an alert accordingly
            if (data.success) {
                alert('Contact updated successfully!');
                fetchContacts();
                document.getElementById('editModal').style.display = 'none';
            } else {
                alert('Failed to update the contact.');
            }
        });
    });

    // Add an event listener to the entire document to handle delete button clicks
    document.addEventListener('click', function(event) {
        if (event.target.closest('.deleteButton')) {
            // Get the contact ID from the clicked button
            const contactId = event.target.closest('.deleteButton').getAttribute('data-id');
            
            // Send a request to the server to delete the contact
            fetch('contacts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + contactId
            })
            .then(response => response.json())
            .then(data => {
                // Check the server's response and display an alert accordingly
                if (data.success) {
                    event.target.closest('tr').remove();
                    alert('Contact deleted successfully!');
                } else {
                    alert('Failed to delete the contact.');
                }
            });
        }
    });

    // Add an event listener to the next page button
    document.getElementById('nextPage').addEventListener('click', function() {
        currentPage++;
        fetchContacts();
    });

    // Add an event listener to the previous page button
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            fetchContacts();
        }
    });

    // Fetch the initial set of contacts
    fetchContacts();
});

</script>

<style>
    /* Galaxy themed search form styles */
    #searchForm {
        position: absolute;
        left: 1%;
        top: 5px;
        width: 240px;
        background: rgba(10, 24, 61, 0.8); /* Deep space purple with some transparency */
        border-radius: 5px;
        padding: 5px;
        box-shadow: 0 0 10px rgba(140, 20, 252, 1 ); /* Subtle glow around the form */
        padding: 10px;
    }

    #searchForm input[type="text"] {
        width: 60%; /* Reduce width to prevent overlap */
        padding: 5px;
        border: none;
        border-radius: 3px;
        background: rgba(255, 255, 255, 0.1); /* Slightly transparent background */
        color: #FFF; /* White text */
        font-size: 14px;
        outline: none;
    }

    #searchForm input[type="text"]::placeholder {
        color: #DDD; /* Lighter placeholder text */
    }

    #searchForm input[type="submit"] {
        width: 25%;
        border: none;
        border-radius: 3px;
        background: rgba(140, 20, 252, 1 ); /* Bright star purple */
        color: #FFF; /* White text */
        cursor: pointer;
        transition: background 0.3s;
    }

    #searchForm input[type="submit"]:hover {
        background: #0277BD; /* Darker purple on hover */
    }


    .box {
        max-width: 100%; /* Ensure the box doesn't overflow its container */
        overflow-x: auto; /* Allow horizontal scrolling if the content is too wide */
        max-height: 70vh; /* Set a maximum height relative to the viewport */
        overflow-y: auto; /* Allow vertical scrolling if the content is too tall */
        margin-bottom: 10px; /* Add some space at the bottom for breathing room */
        overflow: auto;
        position: absolute;
        bottom: 50px; /* Increase this value to move the box up */
    }

    .pagination-controls{
        display: flex;
        align-items: center;
        gap: 10px; /* space between items */
        padding: 5px 10px;
        background: rgba(10, 24, 61, 0.8); /* Deep space purple with some transparency */
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(140, 20, 252, 1 ); /* Subtle glow around the controls */
        margin-bottom: 0;
        top: 8%;
    }

    .pagination-controls button {
        padding: 5px 10px;
        border: none;
        border-radius: 3px;
        background: rgba(140, 20, 252, 1 ); /* Bright star purple */
        color: #FFF; /* White text */
        cursor: pointer;
        transition: background 0.3s;
    }

    .pagination-controls button:hover {
        background: #0277BD; /* Darker purple on hover */
    }

    .pagination-controls span {
        font-size: 14px;
    }

    /* General button styling */
    .editButton, .deleteButton {
        padding: 5px 10px;
        border: none;
        border-radius: 3px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s;
    }

    /* Edit button styling */
    .editButton {
        background: #03A9F4; /* Bright star purple */
        color: #FFF; /* White text */
    }

    .editButton:hover {
        background: #0277BD; /* Darker purple on hover */
    }

    /* Delete button styling */
    .deleteButton {
        background: #FF4B4B; /* Bright red for delete actions */
        color: #FFF; /* White text */
    }

    .deleteButton:hover {
        background: #D43A3A; /* Darker red on hover */
    }

        /* General styles for all devices */
    .box {
        max-width: 100%; /* Ensure the box doesn't overflow its container */
        overflow-x: auto; /* Allow horizontal scrolling if the content is too wide */
    }

    .contact-table {
        width: 100%; /* Make the table take up the full width of its container */
    }

    .adaptive-text::before {
        content: attr(data-text);
        display: none; /* Initially hide the data-text content */
    }

    .adaptive-text span {
        display: inline; /* Display the original text by default */
    }

    .adaptive-text.add-contact::before {
        content: "+";
        display: none; /* Initially hide the + symbol */
    }

    .adaptive-text:not(.add-contact)::before {
        content: "-";
        display: none; /* Initially hide the - symbol */
    }

    /* Media query for screens smaller than 768px (e.g., mobile devices) */
    @media (max-width: 767px) {
        #contactsTitle {
                display: none; /* Hide the Contacts title */
        }
        #searchForm {
            width: 80%; /* Adjusted width for smaller screens */
            left: 10%; /* Center the form by setting equal left and right values */
            right: 10%;
        }

        .pagination-controls {
            flex-direction: row; /* Make the controls horizontal */
            justify-content: center; /* Center the controls horizontally */
            padding: 2px 5px; /* Reduce vertical padding */
            top: 10.5%;
        }
        .pagination-controls button, .pagination-controls span {
            font-size: 12px; /* Reduce font size */
            padding: 2px 5px; /* Reduce padding for buttons */
            margin: 0 2px; /* Add a small margin between items */
        }

        .adaptive-text {
            font-size: 6px; /* Hide the original text by setting font size to 0 */
            content: "+";
        }

        .adaptive-text.add-contact::before {
            content: "+";
            display: inline-block; /* Display the + symbol */
            font-size: 16px; /* Set an appropriate font size for the symbols */
            vertical-align: middle; /* Align the symbol vertically */
        }

        .adaptive-text:not(.add-contact)::before {
            content: "-";
            display: inline-block; /* Display the - symbol */
            font-size: 16px; /* Set an appropriate font size for the symbols */
            vertical-align: middle; /* Align the symbol vertically */
        }

        #searchMobile {
            padding: 10px 15px; /* Increase padding for a larger button */
            font-size: 8px; /* Increase font size for better readability */
        }
    }

    button[onclick="window.location.href = 'index.php';"] {
        position: absolute;
        right: 0.5%;
        top: 1%;
    }

    button[onclick="window.location.href = 'addcontacts.php';"] {
        position: absolute;
        right: 15%;
        top: 1%;
    }

</style>

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

        <!-- Sign out link -->
        <button style="right: 0.5%; top: 1%;" class="glow-on-hover" onclick="window.location.href = 'index.php';">
            <span>Sign Out</span>
        </button>
        <!-- Add New Contact -->
        <button style="right: 15%; top: 1%;" class="glow-on-hover add-contact" onclick="window.location.href = 'addcontacts.php';">
            <span>Add New Contact</span>
        </button>
        
        
        <!-- Contacts title -->
        <div class="main">
            <h1 class="floating glowing title" id="contactsTitle">Contacts</h1>

            <!-- Search Form -->
            <form id="searchForm" style="position: absolute;left:1%; top: 5px;width:240px;">
                <input type="text" name="search" id="searchInput" placeholder="Search by name">
                    <input type="submit" value="Search" id = "searchMobile">
            </form>
            
            <!-- Table for displaying contacts -->
            <div class="box">
            <table class="contact-table">
                <thead>
                    <tr>
                        <th style="color: white;">Name</th>
                        <th style="color: white;">Email</th>
                        <th style="color: white;">Phone</th>
                        <th style="color: white;">Date</th>
                        <th style="color: white;">Edit</th>
                        <th style="color: white;">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- New rows will be appended here -->
                </tbody>
            </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="pagination-controls" style="position: absolute;left:2%;">
            <button id="prevPage">&laquo; Prev</button>
            <span style = "color:white">Page: <span id="currentPage">1</span></span>
            <button id="nextPage">Next &raquo;</button>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" style="display:none; position:fixed; top:20%; left:30%; width:40%; background: linear-gradient(45deg, #000, #001F3F, #000, #001F3F, #000); padding:20px; border:1px solid white; border-radius: 10px;">
            <h2 style="color: white;">Edit Contact</h2>
            <form id="editForm">
                <input type="hidden" id="editId" name="editId">
                <div style="color: white;">
                    <label for="editName">Name:</label>
                    <input type="text" id="editName" name="editName" style="color: white; background: transparent; border-bottom: 1px solid white;">
                </div>
                <div style="color: white;">
                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="editEmail" style="color: white; background: transparent; border-bottom: 1px solid white;">
                </div>
                <div style="color: white;">
                    <label for="editPhone">Phone:</label>
                    <input type="text" id="editPhone" name="editPhone" style="color: white; background: transparent; border-bottom: 1px solid white;">
                </div>
                <input type="submit" value="Update" style="background: rgba(10, 24, 61, 0.8); color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px;">
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" style="background: rgba(10, 24, 61, 0.8); color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px;">Close</button>
            </form>
        </div>
    </body>
</html>