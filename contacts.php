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
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;

    function fetchContacts() {
        let searchValue = document.getElementById('searchInput').value;
        let fetchUrl = `contacts.php?page=${currentPage}`;
        
        if (searchValue) {
            fetchUrl += '&search=' + encodeURIComponent(searchValue);
        }
        
        fetch(fetchUrl, {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(contacts => {
            let tbody = document.querySelector('.contact-table tbody');
            tbody.innerHTML = '';
            
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

            // Update the displayed current page
            document.getElementById('currentPage').textContent = currentPage;
        });
    }

    document.getElementById('searchForm').addEventListener('submit', function(event) {
        event.preventDefault();
        fetchContacts();
    });

    document.getElementById('searchInput').addEventListener('input', function() {
        fetchContacts();
    });

    document.addEventListener('click', function(event) {
        if (event.target.closest('.editButton')) {
            const contactId = event.target.closest('tr').querySelector('.deleteButton').getAttribute('data-id');
            const contactName = event.target.closest('tr').querySelector('td:nth-child(1)').textContent;
            const contactEmail = event.target.closest('tr').querySelector('td:nth-child(2)').textContent;
            const contactPhone = event.target.closest('tr').querySelector('td:nth-child(3)').textContent;

            document.getElementById('editId').value = contactId;
            document.getElementById('editName').value = contactName;
            document.getElementById('editEmail').value = contactEmail;
            document.getElementById('editPhone').value = contactPhone;

            document.getElementById('editModal').style.display = 'block';
        }
    });

    document.getElementById('editForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        fetch('contacts.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Contact updated successfully!');
                fetchContacts();
                document.getElementById('editModal').style.display = 'none';
            } else {
                alert('Failed to update the contact.');
            }
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
                    event.target.closest('tr').remove();
                    alert('Contact deleted successfully!');
                } else {
                    alert('Failed to delete the contact.');
                }
            });
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        currentPage++;
        fetchContacts();
    });

    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            fetchContacts();
        }
    });

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
        <button style="right: 0.5%; top: 1%;"class="glow-on-hover" onclick="window.location.href = 'index.php';">
            Sign Out
        </button>

        <button style="right: 15%; top: 1%;"class="glow-on-hover" onclick="window.location.href = 'addcontacts.php';">
            Add New Contact
        </button>
        
        
        <!-- Contacts title -->
        <div class="main">
            <h1 class="floating glowing title">Contacts</h1>

            <form id="searchForm" style="position: absolute;left:1%; top: 5px;width:240px;">
                <input type="text" name="search" id="searchInput" placeholder="Search by name">
                    <input type="submit" value="Search">
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
        <div class="pagination-controls" style="position: absolute;left:2%; top:7%">
            <button id="prevPage">&laquo; Prev</button>
            <span style = "color:white">Page: <span id="currentPage">1</span></span>
            <button id="nextPage">Next &raquo;</button>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" style="display:none; position:fixed; top:20%; left:30%; width:40%; background-color:white; padding:20px; border:1px solid black;">
            <h2>Edit Contact</h2>
            <form id="editForm">
                <input type="hidden" id="editId" name="editId">
                <div>
                    <label for="editName">Name:</label>
                    <input type="text" id="editName" name="editName">
                </div>
                <div>
                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="editEmail">
                </div>
                <div>
                    <label for="editPhone">Phone:</label>
                    <input type="text" id="editPhone" name="editPhone">
                </div>
                <input type="submit" value="Update">
                <button type="button" onclick="document.getElementById('editModal').style.display='none'">Close</button>
            </form>
        </div>
    </body>
</html>