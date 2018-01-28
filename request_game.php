<?php
require_once("redsox_utilities.inc");

session_start();
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];

if ($_GET && is_numeric($_GET['gameID'])) {
	$game_id = $_GET['gameID'];
}

if (is_numeric($user_id)) {
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
		} else if (mysql_num_rows($result) > 0) {
			$error_message = "You have already requested this game.";
			$success = false;
		} else {
			// Update requests table
			$command = "SELECT * FROM requests WHERE gameID='".addslashes($game_id)."' AND user_id='".addslashes($user_id)."';";
			$result = mysql_query($command);
			if ($result == false) {
				$error_message = "Unable to verify request entry: ".mysql_error();
				$success = false;
			} else if (mysql_num_rows($result) > 0) {
				$command = "UPDATE requests SET date_deleted='' WHERE gameID='".addslashes($game_id)."' AND user_id='".addslashes($user_id)."';";
				$result = mysql_query($command);
				if ($result == false) {
					$error_message = "Unable to update request table entry: ".mysql_error();
					$success = false;
				}
			} else {
				$command = "INSERT INTO requests VALUES('', '".addslashes($game_id)."', '".addslashes($user_id)."', now(), '');";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "Error updating requests table: ".mysql_error();
					$success = false;
				}
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
				mail("rlm@teapothill.org", 
					"Red Sox Game Request - ".$game_id, 
					"Requesting: <a href=http://".$_SERVER['SERVER_NAME']."/redsox/edit_game.php?gameID=".$game_id."'>".$game_data['date']."</a> - ".$game_data['city']." ".$game_data['name'], 
					"From: ".$user_data['email']);
				$error_message = "Successfully requested game id: ".$game_id;
			} else {
				mail("robert.l.mathews@gmail.com", 
					"Red Sox Game Request", 
					"Request for unknown game: ".$game_id, 
					"From: ".$user_data['email']);
				$error_message = "Successfully requested game... Wait? Which game did you want?";
			}
		}
	}
} else {
	$error_message = "You must be logged in to make a game request.";
}

if ($error_message) {
	echo $error_message;
}

?>

