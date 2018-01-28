<?php
require_once("redsox_utilities.inc");

session_start();
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];

if ($_GET['year']) {
	$year = $_GET['year'];
} else {
	$year = date('Y', time());
}
$pageTitle = "By User";
include("redsox_title.php");

if (!is_admin($user_id)) {
	echo "Only administrators may see the results by user.";
} else {
	$db = admin_db_connect();

	if ($_POST && is_numeric($_POST['gameid'])) {
		mark_paid($_POST['gameid'], $db);
	}

	echo "<h2>".$year."</h2>\n";
	echo "<a href='by_status.php'>Games By Status</a>\n";

	$command = "SELECT ul.user_id, ul.login, ui.name FROM user_login ul JOIN user_info ui ON ui.user_id = ul.user_id ORDER BY login ASC;";
	$users_result = mysql_query($command);

	if ($users_result == false) {
		$error_message = "Error retrieving users data: ".mysql_error();
	} else if (mysql_num_rows($users_result) > 0) {
		while ($this_user = mysql_fetch_assoc($users_result)) {
			// Get price total
			$command = "SELECT SUM(g.price * g.num_tickets) AS total, COUNT(g.gameID) as count FROM games g 
					WHERE g.soldto = '".addslashes($this_user['user_id'])."'
					AND YEAR(g.date) = '".addslashes($year)."';";
			$total_result = mysql_query($command);

			if ($total_result == false) {
				echo "Error calculating total: ".mysql_error();
			} else {
				$total_data = mysql_fetch_assoc($total_result);
				$total = $total_data['total'];
				$count = $total_data['count'];
			}

			$command = "SELECT g.gameID, g.date, ui.name, t.city, t.name as team_name, s.string AS status_text, (g.price * g.num_tickets) as price, g.status_id
					FROM games g 
					LEFT JOIN user_info ui ON ui.user_id = g.soldto
					LEFT JOIN user_login ul ON ul.user_id = g.soldto
					JOIN teams t ON t.id = g.team_id 
					JOIN status s ON s.id = g.status_id
					WHERE g.soldto = ".addslashes($this_user['user_id'])." 
					AND YEAR(g.date) = ".addslashes($year)." AND ul.date_deleted <= 0 ORDER BY g.date ASC;";
			
			$games_result = mysql_query($command);

			if ($games_result == false) {
				$error_message = "Error getting user's ".$this_user['login']." games: ".mysql_error();
			} else if (mysql_num_rows($games_result) > 0) {
				printf("<h4><a href='profile.php?profileID=%s'>%s</a> Total: $%d (%d games)</h4>", $this_user['user_id'], $this_user['name'], $total, $count);

				echo "<table align='center'>\n";
				echo "<th width='50'>Date</th><th width='200'>Team</th><th width='200'>SoldTo</th><th width='50'>Price</th><th width='150'>Status</th><th width='75'>Mark Paid</th>\n";
					while ($game_data = mysql_fetch_assoc($games_result)) {
						printf("<tr><td align='center'><a href='game.php?gameID=%s'>%s</a></td><td align='center'>%s %s</td><td align='center'>%s</td><td align='center'>%s</td><td align='center'>%s</td><td align='center'><form method='POST' action='by_user.php?profileID=%s'><input type='hidden' name='gameid' value='%s'>%s</form></td></tr>\n", 
							$game_data['gameID'],
							$game_data['date'], 
							$game_data['city'], 
							$game_data['team_name'], 
							$game_data['name'], 
							$game_data['price'],
							$game_data['status_text'],
							$this_user['user_id'],
							$game_data['gameID'],
							$game_data['status_id'] == 2 ? "<input type='submit' value='Mark Paid'>" : "");
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

				


