<?php
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "contactmanager";

    // Create connection
    $conn = new mysqli($server, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL to create users table
    $usersql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    // Check connection
    if (!$conn->query($usersql)) {
        echo "Error creating users table: " . $conn->error;
    }

    // SQL to create contacts table
    $contactsql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        userID INT(6) UNSIGNED NOT NULL,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (userID) REFERENCES users(id)
    )";

    // Check connection
    if (!$conn->query($contactsql)) {
        echo "Error creating contacts table: " . $conn->error;
    }

?>
