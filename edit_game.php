<?php

require("redsox_utilities.inc");

session_start();

$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];
$game_id = $_GET['gameID'];

if (!is_numeric($user_id)) {
	$_SESSION['referal'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
}

if (!$game_id && $_POST) {
	$game_id = $_POST['gameID'];
}

if (is_numeric($game_id)) {
	if (!is_admin($user_id)) {
		$error_message = "Only Administrators are allowed to edit game records.";
	} else if ($_POST && count($_POST) > 0) {
		// Handle edits
		$date = $_POST['date'];
		$time = $_POST['time'];
		$team_id = $_POST['team'];
		$price = $_POST['price'];
		$status_id = $_POST['status'];
		$num_tickets = $_POST['num_tickets'];

		if (is_numeric($num_tickets)) {
			if ($num_tickets > 2 || $num_tickets < 0) {
				$num_tickets = 2;
			}
		} else {			
			$num_ticket = 2;
		}

		$soldto = $_POST['soldto'];

		if ($soldto == '') {
			$soldto = 0;
		}

		if (!($date && $time && $team_id && $price && status_id)) {
			$error_message = "Please provide all of the required fields.";
		} else if (strtotime($date) == false) {
			$error_message = "Date value is invalid.";
		} else if (strtotime($time) == false) {
			$error_message = "Time value is invalid.";
		} else if (!is_numeric($team_id) || $team_id <= 0) {
			$error_message = "Team value is invalid.";
		} else if (!is_numeric($price) || $price < 0) {
			$error_message = "Price value is invalid.";
		} else if (!is_numeric($status_id) || $status_id < 0) {
			$error_message = "Status value is invalid.";
		} else if (!is_numeric($soldto) || $soldto < 0) {
			$error_message = "User is invalid.";
		} else { 
		
			$db = admin_db_connect();

			// Verify the db constraints
			$success = true;

			$command = "SELECT id FROM teams WHERE id = '".addslashes($team_id)."';";
			$result = mysql_query($command);

			if ($result == false) {
				$error_message = "Error validating team: ".mysql_error();
				$success = false;
			} else if (mysql_num_rows($result) <= 0) {
				$error_message = "No matching team in the database.";
				$success = false;
			}

			if ($success) {
				$command = "SELECT id FROM status WHERE id='".addslashes($status_id)."';";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "Error validating status value: ".mysql_error();
					$success = false;
				} else if (mysql_num_rows($result) <= 0) {
					$error_message = "No matching status in the database.";
					$success = false;
				}
			}

			if ($success && $soldto != 0) {
				$command = "SELECT user_id FROM user_login WHERE user_id='".addslashes($soldto)."';";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "Error validating user: ".mysql_error();
					$success = false;
				} else if (mysql_num_rows($result) <= 0) {
					$error_message = "No matching user in the database.";
					$success = false;
				}
			}

			if ($success && is_admin($user_id)) {
				$date_string = date('Y-m-d', strtotime($date));
				$time_string = date('H:i', strtotime($time));

				$command = "UPDATE games SET date='".addslashes($date_string)."',
						time='".addslashes($time_string)."',
						team_id='".addslashes($team_id)."',
						price='".addslashes($price)."',
						status_id='".addslashes($status_id)."',
						soldto='".addslashes($soldto)."',
						num_tickets='".addslashes($num_tickets)."'
						WHERE gameID='".addslashes($game_id)."';";

				$result = mysql_query($command);
				if ($result == false) {
					$error_message = "Error updating game record: ".mysql_error();
				} else {
					//SUCCESS!
					header("Location: game.php?gameID=".$game_id);
				}
			}
		}
	}

	if (!is_admin($user_id)) {
		$error_message = "Only administrators may edit game records.";
	} else {
		if (!$db) {
			$db = admin_db_connect();
		}

		$game_data = fetch_game($game_id, $db);
	
		$bos_command = "SELECT * FROM teams WHERE name='Red Sox';";
		$bos_result = mysql_query($bos_command);
		$bos_data = mysql_fetch_assoc($bos_result);

                $pageTitle = "Edit Game " . addslashes($game_id) . " | " . $game_data['date'];
// Start HTML page
		include ("redsox_title.php");

                printf("<a href='game_requests.php?gameID=%s'>Game Requests</a> | <a href='game.php?gameID=%s'>View Game</a>", $game_id, $game_id);

		if ($error_message) {
			echo $error_message;
		}
?>
		<form action='edit_game.php' method='post'>
		<table align='center'>
		<tr><td colspan='2' align='center'><img src='<?php echo $bos_data['icon_url']; ?>'> vs. <img src='<?php echo $game_data['icon_url']; ?>'></td></tr>
		<tr><td align='right' width='50%'>Date:</td><td align='left' width='50%'><input type='text' name='date' value='<?php echo $game_data['date']; ?>'></td></tr>
		<tr><td align='right'>Time:</td><td align='left'><input type='text' name='time' value='<?php echo date('g:i a', strtotime($game_data['time'])); ?>'></td></tr>
		<tr><td align='right'>Team:</td><td align='left'>
			<select name='team'>
		   <?php	$teams = fetch_teams($db);

			foreach($teams as $team) {
				printf("<option value='%s'%s>%s</option>\n", 
					$team['id'], ($game_data['name'] == $team['name'] ? " selected='selected'" : ""), $team['city']." ".$team['name']);
			}  ?>
			</select></td></tr>
		<tr><td align='right'>Ticket Price:</td><td align='left'><input type='text' name='price' value='<?php echo $game_data['price']; ?>'></td></tr>
		<tr><td align='right'>Status:</td><td align='left'>
			<select name='status'>
		   <?php	$status_array = fetch_statuses($db);

			foreach($status_array as $this_status) {
				printf("<option value='%s'%s>%s</option>\n",
					$this_status['id'], ($game_data['status_text'] == $this_status['status_text'] ? " selected='selected'" : ""), $this_status['status_text']);
			} ?>
			</select></td></tr>
		<tr><td align='right'>Sold To:</td><td align='left'>
			<select name='soldto'><option value=''<?php printf("%s", ($game_data['soldto'] > 0 ? " selected='selected'" : "")); ?>>Choose a name</option>
		   <?php	$profiles_array = fetch_profiles($db);
			echo count($profiles_array);

			foreach ($profiles_array as $this_profile) {
				printf("<option value='%s'%s>%s</option>\n",
					$this_profile['user_id'], ($game_data['soldto'] == $this_profile['user_id'] ? " selected='selected'" : ""), $this_profile['name']);
			}  ?>
			</select></td></tr>
		<tr><td align='right'>Number of Tickets:</td><td align='left'>
			<select name='num_tickets'>
		<?php	for ($i = 0; $i <= 4; ++$i) {
				if ($i == 3)
					continue;

				printf("<option%s>%s</option>\n", $game_data['num_tickets'] == $i ? " selected='selected'" : "", $i);
			} ?>
			</select>
		</td></tr>
		<tr><td colspan='2' align='right'>
			<input type='hidden' name='gameID' value='<?php echo $game_id; ?>'>
			<input type='submit' value='Save'>
		</td></tr>
		</table>
		</form>
<?
	}
} else {
	$error_message = "Bad gameID value.";
}

echo $error_message;
?>
