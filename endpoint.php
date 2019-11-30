<?php
ob_end_clean();
$receivedPost = json_decode(file_get_contents('php://input'), true);
// check acceptable incoming action
// action - create


if ($receivedPost['action'] == 'create') {
	// expected parameters 
	/*
	firstName
	lastName
	phoneNumber
	emailAddress
	residentialAddress
	dob
	*/
	// checkpoint - all parameters required
	if (empty($receivedPost['firstName']) || 
		empty($receivedPost['lastName']) || 
		empty($receivedPost['phoneNumber']) || 
		empty($receivedPost['emailAddress']) ||
		empty($receivedPost['residentialAddress']) || 
		empty($receivedPost['dob'])
	) {
		// return an errror, parameters provided is insufficient
		header('Content-Type: application/json');
		die (json_encode([
					"responseCode" => 400,
					"respons)eMeaning" => "Bad Request - ALl Parameters are required"
		]));
	} else  {
		// authentication checks completed
		// filter contents
		$staffDetails = [];
		$staffDetails['id'] = $staffid = uniqid();
		$staffDetails['firstName'] = addslashes($receivedPost['firstName']);
		$staffDetails['lastName'] = addslashes($receivedPost['lastName']);
		$staffDetails['phoneNumber'] = addslashes($receivedPost['phoneNumber']);
		$staffDetails['emailAddress'] = addslashes($receivedPost['emailAddress']);
		$staffDetails['residentialAddress'] = addslashes($receivedPost['residentialAddress']);
		$staffDetails['dob'] = addslashes($receivedPost['dob']);
		$staffDetails['status'] = true; // used to handle staff enabled status

		$staffDetails = json_encode($staffDetails);
		$insertQuery = "INSERT INTO staffrecords.staffdetails (staff_uid, staff_data) VALUES ('$staffid', '$staffDetails')";

		// database connection
		if (insertQuery($insertQuery)) {
			// return details
			$response = [];
			$response['responseCode'] = 200;
			$response['data']['staffid'] = $staffid;


			header('Content-Type: application/json');
			die (json_encode([
				"responseCode" => 200,
				"staffid" => $staffid
			]));
		} else {

			header('Content-Type: application/json');
			die (json_encode([
				"responseCode" => 500,
				"responseMeaning" => 'Internal Server Error - Your request could not be completed'
			]));
		}

	}
} else if ($receivedPost['action'] == 'disable') {
	// disable staff details
	// expected data - staff id 
	//staffId
	$staffid = addslashes($receivedPost['staffId']);
	// check if staff details exists in database
	if (empty($staffid)) {

		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 400,
			"responseMeaning" => "Bad Request - ALl Parameters are required"
		]));
	}

	$query = "SELECT staff_data->>'$.status' FROM staffrecords.staffdetails WHERE staff_uid = '$staffid'";
	$query = selectQuery ($query);
	// check status to see if status is already disabled
	// to prevent another database call
	if ( $query ) {

		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 200,
			"message" => "Staff if {$staffid} disabled successfully"
		]));
	} else {
		// disable data
		$updateQuery = "UPDATE staffdetails SET staff_data = JSON_REPLACE(staff_data, '$.status', 'false')";
		$updateQuery = dbQuery ($updateQuery);
		if ($updateQuery) {

		header('Content-Type: application/json');
			die (json_encode([
				"responseCode" => 200,
				"message" => "Staff if {$staffid} disabled successfully"
			]));
		} else {

		header('Content-Type: application/json');
			die (json_encode([
				"responseCode" => 500,
				"responseMeaning" => 'Internal Server Error - Your request could not be completed'
			]));
		}
	}
} else if ($receivedPost['action'] == 'delete') {
	// disable staff details
	// expected data - staff id 
	//staffId
	$staffid = addslashes($receivedPost['staffId']);
	// check if staff details exists in database
	if (empty($staffid)) {

		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 400,
			"responseMeaning" => "Bad Request - ALl Parameters are required"
		]));
	}
	$query = "SELECT staff_uid FROM staffrecords.staffdetails WHERE staff_uid = '$staffid'";
	$query = selectQuery ($query);
	// check if details exists
	if ($query['staff_uid']) {
		// delete record
		$deleteQuery = "DELETE FROM staffrecords.staffdetails WHERE staff_uid = '$staffid'";
		$deleteQuery = dbQuery($deleteQuery);
		if ($deleteQuery) {

		header('Content-Type: application/json');
			die (json_encode([
				"responseCode" => 200,
				"message" => "Staff if {$staffid} deleted successfully"
			]));
		}
	}else {

		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 400,
			"responseMeaning" => "Bad Request - No record found for id - {$staffid}"
		]));
	}

} else if ($receivedPost['action'] == 'search') {
	// searchable by staff id
	$staffid = addslashes($receivedPost['staffid']);
	if (empty($staffid)) {

		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 400,
			"responseMeaning" => "Bad Request - ALl Parameters are required"
		]));
	}
	$query = "SELECT staff_uid, staff_data FROM staffrecords.staffdetails WHERE staff_uid = '$staffid'";
	$query = selectQuery ($query);
	if ($query['staff_uid']) {

		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 200,
			"data" => $query['staff_data']
		]));	
	} else {

		header('Content-Type: application/json');
		// no records found
		die (json_encode([
			"responseCode" => 400,
			"responseMeaning" => "Bad Request - No record found for id - {$staffid}"
		]));
	}
} else if ($receivedPost['action'] == 'getStaff') {
	// acceptable
	// limits - limit is expected to be an integer
	$applicableLimits = addslashes($receivedPost['limits']);
	if (is_int($applicableLimits)) {
		$selectQuery = "SELECT staff_data FROM staffrecords.staffdetails LIMIT $applicableLimits";
		$selectQuery = selectQuery($selectQuery);
		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 400,
			"responseMeaning" => $selectQuery['staff_data']
		]));
	} else {
		header('Content-Type: application/json');
		die (json_encode([
			"responseCode" => 400,
			"responseMeaning" => "Bad Request - Only integers are accepted for this endpoint"
		]));
	}
}


function dbConnection () {
	$servername = "localhost";
	$username = "localHost";
	$password = "Utuk1j0VP@.";
	$conn = new mysqli($servername, $username, $password);
	return $conn;

}


function insertQuery ( $query ) {
	$conn = dbConnection();
	if ( $conn ) {
		if ($conn->query($query)) return true;
		else return false;
	} else return false;
}


function dbQuery ( $query ) {
	$conn = dbConnection();
	if ( $conn ) {
		if ($conn->query($query)) return true;
		else return false;
	} else return false;
}

function selectQuery ($query) {
	$connection = dbConnection();
	if ( $connection ) {
		$data = mysqli_query($connection, $query);
		$data = mysqli_fetch_assoc($data);
		return $data;
	}else return false;
}

?>
