<?php
require_once("redsox_utilities.inc");

include ("session_vars.inc");

$game_id = get_var('game_id');
if (!is_numeric($game_id)) {
	$game_id = "";
}

if (is_numeric($user_id)) {
	if (is_admin($user_id)) {
		$request_id = get_var('requestID');
		if (!is_numeric($request_id)) {
			echo "Bad Request ID: ".$request_id."\n";
		} else {
			$db = admin_db_connect();
			$command = "SELECT * FROM requests WHERE request_id='".addslashes($request_id)."';";
			$result = mysql_query($command);

			if ($result == false) {
				echo "Error validating request id: ".mysql_error();
			} else if (mysql_num_rows($result) <= 0) {
				echo "Invalid request id: ".$request_id." (".$command.")";
			} else {
				$command = "UPDATE requests SET date_deleted = now() WHERE request_id = '".addslashes($request_id)."';";
				$result = mysql_query($command);

				if ($result == false) {
					echo "Error deleting request: ".mysql_error();
				} else if (mysql_affected_rows() <= 0) {
					echo "No rows updated.";
				} else {
					//Success
					if ($_SERVER['redirect']) {
						$redirect = $_SERVER['redirect'];
						$_SERVER['redirect'] = "";
					} else {
						$redirect = "pending_requests.php";
					}
					
					header("Location: ".$redirect);
				}
			}
			
		}
	}
	
	$db = member_db_connect();
	$user_data = array();
	$game_data = array();

	if ($game_id) {	
		$command = "SELECT g.date, t.city, t.name FROM games g JOIN teams t ON (g.team_id = t.id)
			WHERE gameID='".addslashes($game_id)."';";
		$result = mysql_query($command);

		if ($result != false) {
			$game_data = mysql_fetch_assoc($result);
		}
	}

	$success = true;

	if (count($game_data) > 0) {
		//Check for existing request
		$command = "SELECT * FROM requests WHERE gameID='".addslashes($game_id)."' AND user_id='".addslashes($user_id)."'
			AND date_deleted <= 0;";
		$result = mysql_query($command);

		if ($result == false) {
			$error_message = "Unable to verify requests table: ".mysql_error();
			$success = false;
		} else if (mysql_num_rows($result) <= 0) {
			$error_message = "You have not requested this game.";
			$success = false;
		} else {
			// Update requests table
			$command = "UPDATE requests SET date_deleted=now() WHERE gameID='".addslashes($game_id)."' AND user_id='".addslashes($user_id)."';";
			$result = mysql_query($command);

			if ($result == false) {
				$error_message = "Error updating requests table: ".mysql_error();
				$success = false;
			}
		}
	} else {
		$error_message = "Error retrieving game data.";
		$success = false;
	}

	if ($success) {
		$command = "SELECT ui.name, ui.email FROM user_info ui WHERE user_id='".addslashes($user_id)."'";
		$result = mysql_query($command);

		if ($result == false) {
			$error_message = "Error retrieving user data: ".mysql_error();
		} else if (mysql_num_rows($result) <= 0) {
			$error_message = "Error fetching user profile.";
		} else {
			$user_data = mysql_fetch_assoc($result);
			if (is_numeric($game_id)) {
				mail("robert.l.mathews@gmail.com", 
					"Red Sox Game Request Cancelation - ".$game_id, 
					$user_data['name']." has canceled the request for: ".$game_data['date']." - ".$game_data['city']." ".$game_data['name'], 
					"From: ".$user_data['email']);
				$error_message = "Successfully canceled request for game id: ".$game_id;
			} else {
				mail("robert.l.mathews@gmail.com", 
					"Red Sox Game Request Cancelation", 
					$user_data['name']." has canceled the request for an unknown game: ".$game_id, 
					"From: ".$user_data['email']);
				$error_message = "Successfully canceled the request for game... Wait? Which game did you want?";
			}
		}
	}
} else {
	$error_message = "You must be logged in to cancel a game request.";
}

if ($error_message) {
	echo $error_message;
}

?>

