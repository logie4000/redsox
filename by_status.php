<?php
require_once("redsox_utilities.inc");

session_start();
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];

if (is_numeric($_GET['year'])) {
	$year = $_GET['year'];
} else {
	$year = date('Y', time());
}

$pageTitle = "By Status";

if (is_numeric($_GET['profileID'])) {
	$profile_id = $_GET['profileID'];
}

include("redsox_title.php");

if (!is_admin($user_id)) {
	if ($profile_id != $user_id) {
		$shouldContinue = false;
	} else {
		$shouldContinue = true;
	}
} else {
	$shouldContinue = true;
}

if (!$shouldContinue) {
	echo "Only administrators can see results by status.";
} else {
	if (is_admin($user_id)) {
		$db = admin_db_connect();
	} else {
		$db = member_db_connect();
	}

	if (is_admin($user_id) && $_POST && is_numeric($_POST['gameid'])) {
		// Mark game as paid
		mark_paid($_POST['gameid'], $db);
	}	
	
	echo "<h2>".$year."</h2>\n";

	if (is_admin($user_id)) {
		echo "<a href='by_user.php'>Games By User</a>\n";
	}

	$command = "SELECT * FROM status;";
	$status_result = mysql_query($command);

	if ($status_result == false) {
		$error_message = "Error retrieving status data: ".mysql_error();
	} else if (mysql_num_rows($status_result) <= 0) {
		$error_message = "No status data.";
	} else {
		while ($this_status = mysql_fetch_assoc($status_result)) {
			// Get price total
			$command = "SELECT SUM(g.price * g.num_tickets) AS total, COUNT(g.gameID) as count FROM games g 
					WHERE g.status_id = '".addslashes($this_status['id'])."'
					AND YEAR(g.date) = '".addslashes($year)."'";
			if (is_numeric($profile_id)) {
				$command .= " AND g.soldto = '".addslashes($profile_id)."'";
			}

			$command .= ";";

			$total_result = mysql_query($command);

			if ($total_result == false) {
				echo "Error calculating total: ".mysql_error();
			} else {
				$total_data = mysql_fetch_assoc($total_result);
				$total = $total_data['total'];
				$count = $total_data['count'];
			}

			$command = "SELECT g.gameID, g.date, ui.name, t.city, t.name as team_name, s.string AS status_text, (g.price * g.num_tickets) as price, g.soldto 
					FROM games g 
					LEFT JOIN user_info ui ON ui.user_id = g.soldto
					LEFT JOIN user_login ul ON ul.user_id = g.soldto
					JOIN teams t ON t.id = g.team_id 
					JOIN status s ON s.id = g.status_id
					WHERE g.status_id = ".addslashes($this_status['id'])." 
					AND YEAR(g.date) = ".addslashes($year)." AND ul.date_deleted <= 0";
			if (is_numeric($profile_id)) {
				$command .= " AND g.soldto = '".addslashes($profile_id)."'";
			}

			$command .= " ORDER BY g.date ASC;";
			
			$games_result = mysql_query($command);

			if ($games_result == false) {
				$error_message = "Error getting status ".$status." games: ".mysql_error();
			} else if (mysql_num_rows($games_result) > 0) {
				echo "<h4>".$this_status['string']." Total: $".$total." (".$count." games)</h4>";

				echo "<table align='center'>\n";
				echo "<th width='50'>Date</th><th width='200'>Team</th><th width='200'>SoldTo</th><th width='50'>Price</th><th width='50'>Mark Paid</th>\n";
					while ($game_data = mysql_fetch_assoc($games_result)) {
						printf("<tr><td align='center'><a href='game.php?gameID=%s'>%s</a></td><td align='center'>%s %s</td><td align='center'><a href='profile.php?profileID=%s'>%s</a></td><td align='center'>%s</td><td><form method='POST' action='by_status.php?profileID=%s'><input type='hidden' name='gameid' value='%s'>%s</form></td></th>\n", 
							$game_data['gameID'],
							$game_data['date'], 
							$game_data['city'], 
							$game_data['team_name'], 
							$game_data['soldto'],
							$game_data['name'], 
							$game_data['price'],
							$game_data['soldto'],
							$game_data['gameID'],
							$this_status['id'] == 2 ? "<input type='submit' value='Mark Paid'>" : "");
					}

				echo "</table>\n";
			}
		}
	}
}

if ($error_message) {
	echo $error_message;
}
?>

				


