<?php
/*
TODO: try using a Contact object instead of passing around the json
*/

    //grabbing the information for our contact
    $inData = getRequestInfo();

    //try to connect to our database
    $conn = new mysqli("localhost", "root", " ", "StarfinderDB");
	if ($conn->connect_error) {
		returnWithError( $conn->connect_error );
	} 
	else { //insert new contact
		$stmt = $conn->prepare("INSERT into Contacts (FirstName,LastName,Email,Phone,UserID) VALUES(?,?,?,?,?)");
		$stmt->bind_param("sssss",$inData["FirstName"], $inData["LastName"],$inData["Email"], $inData["Phone"], $inData["UserID"]);
		$stmt->execute();
		$stmt->close();
		$conn->close();
		returnWithError("");
	}

    function getRequestInfo(){
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj ){
		header('Content-type: application/json');
		echo $obj;
	}
	
	//TODO: return a status error code
	function returnWithError( $err ){
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

?>