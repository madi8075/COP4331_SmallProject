<?php

    $inData = getRequestInfo();

    $userID = 0;
    $firstName = "";
    $lastName = "";

    $conn = new mysqli("localhost", "root", "", "StarfinderDB");
    if($conn->connect_error){
        returnWithError( $conn->connect_error); //cannot successfully connect to StarfinderDB

    }else{

		$stmt = $conn->prepare("SELECT ID,FirstName,LastName FROM Users WHERE Login=? AND Password =?");
		$stmt->bind_param("ss", $inData["login"], $inData["password"]);
		$stmt->execute();
		$result = $stmt->get_result();

	
		if( $row = $result->fetch_assoc()){
			returnWithInfo( $row['FirstName'], $row['LastName'], $row['ID'] );
		}else{
			echo $inData["login"];
			returnWithError("No Records Found");
		}
	
		$stmt->close();
		$conn->close();
    }

    function getRequestInfo(){
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj ){
		header('Content-Type: application/json');
		echo $obj;
	}
	
	//TODO: return error status code if user not found
	function returnWithError( $err ){
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $firstName, $lastName, $id ){
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}

?>
