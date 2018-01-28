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

include("redsox_title.php");

if (!is_admin($user_id)) {
	echo "Only administrators may see the requested games list.";
} else {
	$db = admin_db_connect();

	echo "<h2>".$year."</h2>\n";

	$command = "SELECT r.gameID, g.date, g.time, t.city, t.name AS team_name, s.string AS status_text FROM requests r
		JOIN games g ON g.gameID=r.gameID
		JOIN teams t ON t.id=g.team_id
		JOIN status s ON s.id=g.status_id
		WHERE YEAR(g.date) = '".addslashes($year)."' AND date_deleted <= 0
		GROUP BY r.gameID ORDER BY g.date ASC;";

	$games_result = mysql_query($command);

	if ($games_result == false) {
		$error_message = "Error retrieving requested games data: ".mysql_error();
	} else if (mysql_num_rows($games_result) > 0) {
		while ($this_game = mysql_fetch_assoc($games_result)) {

			$command = "SELECT ui.name, r.date_requested, r.user_id AS requestor_id, r.request_id
					FROM requests r
					JOIN user_info ui ON ui.user_id = r.user_id
					JOIN user_login ul on ul.user_id = r.user_id
					WHERE r.gameID = ".addslashes($this_game['gameID'])." AND r.date_deleted <= 0 
					AND ul.date_deleted <= 0 ORDER BY r.date_requested ASC;";
			
			$requests_result = mysql_query($command);

			if ($requests_result == false) {
				$error_message = "Error getting requests for ".$this_game['gameID'].": ".mysql_error();
			} else if (mysql_num_rows($requests_result) > 0) {
				echo "<h4><a href='game.php?gameID=".$this_game['gameID']."'>".$this_game['date']."</a></h4>\n";

				echo "<table align='center'>\n";
				echo "<th width='150'>Requestor</th><th width='200'>Team</th><th width='175'>Date Requested</th><th width='150'>Status</th><th width='75'></th>\n";
					while ($request_data = mysql_fetch_assoc($requests_result)) {
						printf("<tr><td align='center'><a href='profile.php?profileID=%s'>%s</a></td><td align='center'>%s %s</td><td align='center'>%s</td><td align='center'>%s</td><td align='center'><a href='cancel_request.php?requestID=%s'>Cancel</a>\n", 
							$request_data['requestor_id'],
							$request_data['name'],
							$this_game['city'], 
							$this_game['team_name'], 
							date('Y-m-d g:i a', strtotime($request_data['date_requested'])), 
							$this_game['status_text'],
							$request_data['request_id']);
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

				


