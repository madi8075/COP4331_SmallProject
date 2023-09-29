<?php

    // Fetch data
    $contactInfo = getRequestInfo();

    // Store info in variables
    $id = $contactInfo['id'];
    $userId = $contactInfo['user_id'];
    $name = $contactInfo['username'];
    $email = $contactInfo['email'];
    $phone = $contactInfo['phone'];

    // Create connection
    $conn = new mysqli("localhost", "root", "9BESTPOOSDGROUP", "contactmanager");
    // Check connection
    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    }
    else
    {
        // Check if the contact belongs to the user
        $confirmUser = $conn->prepare("SELECT * FROM contacts WHERE id = ? AND userID = ?");
        $confirmUser->bind_param("ii", $id, $userId);
        $confirmUser->execute();
        $found = $confirmUser->get_result();

        if ($found->num_rows === 0)
        {
            returnWithError("The contact you are trying to edit was not found / does not belong to this user");
        }
        else
        {
            // If security check is passed then go ahead and update the info
            $confirmUser = $conn->prepare("UPDATE contacts SET username=?, email=?, phone=? WHERE id = ?");
            $confirmUser->bind_param("ssi", $name, $email, $phone, $id);
            $confirmUser->execute();
            $confirmUser->close();
            returnWithError("");
        }

        $confirmUser->close();
        $conn->close();
    }

    function getRequestInfo()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    function sendResultInfoAsJson($obj)
    {
        header('Content-type: application/json');
        echo $obj;
    }

    function returnWithError($err)
    {
        $retValue = '{"error":"' . $err . '"}';
        sendResultInfoAsJson($retValue);
    }

?>