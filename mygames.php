<?php

require_once("redsox_utilities.inc");

include ("session_vars.inc");

$mygames_table = get_var('mode');
if ($mygames_table == "") {
	$mygames_table = 'sold';
}

include("redsox_title.php");

if (is_numeric($user_id)) {
	$db = member_db_connect();
	if ($mygames_table == 'requests') {
		$command = "SELECT g.gameID, g.date, g.time, t.city, t.name, s.string as status_text, g.status_id FROM requests r
			JOIN games g ON (g.gameID = r.gameID) JOIN teams t ON (t.id = g.team_id) JOIN status s ON (s.id = g.status_id)
			WHERE r.user_id = '".addslashes($user_id)."' AND r.date_deleted <= 0 AND YEAR(g.date) >= YEAR(now());";
		$result = mysql_query($command);

		if ($result == false) {
			$error_message = "Error retrieving requested games: ".mysql_error();
		} else if (mysql_num_rows($result) <= 0) {
			echo "No games requested.";
		} else {
			echo "<h2>Requested Games for ".$user_login."</h2>\n";
			echo "<a href='mygames.php?mode=sold'>Show Reserved Games</a><br>\n";

			echo "<table align='center'>\n";
			echo "<th width='50' align='center'>Date</th><th width='75'>Time</th><th width='200' align='center'>Team</th><th width='100' align='center'>Status</th>\n";

			while ($game_data = mysql_fetch_object($result)) {
				printf("<tr bgcolor='d0d0d0'><td align='center'><a href='game.php?gameID=%s'>%s</a></td><td align='center'>%s</td><td align='center'>%s %s</td><td align='center'>%s</td></tr>\n",
					$game_data->gameID, $game_data->date, date('g:i a', strtotime($game_data->time)), $game_data->city, $game_data->name, $game_data->status_text);
			}

			echo "</table>";
		}
	} else {
		$command = "SELECT YEAR(g.date) as year FROM games g WHERE g.soldto='".addslashes($user_id)."' AND YEAR(g.date) >= YEAR(now()) 
			GROUP BY YEAR(g.date) ORDER BY g.date DESC;";
	

//		$command = "SELECT g.date, t.city, t.name, s.string as status FROM games g JOIN teams t ON (g.team_id = t.id)
//			JOIN status s ON (g.status_id = s.id) WHERE g.soldto = '".addslashes($user_id)."' ORDER BY g.date;";

		$result = mysql_query($command);
		if ($result == false) {
			$error_message = "Unable to retrieve game data: ".mysql_error();
		} else if (mysql_num_rows($result) <= 0) {
			$error_message = "No games listed.";
		} else {
			echo "<h2>Reserved Games for ".$user_login."</h2>";
			echo "<a href='mygames.php?mode=requests'>Show Game Requests</a><br>\n";	

			while ($data = mysql_fetch_object($result)) {
				$year = $data->year;
				$year_command = "SELECT g.gameid, g.date, g.time, t.city, t.name, s.string as status FROM games g 
					JOIN teams t ON (g.team_id=t.id) JOIN status s ON (g.status_id = s.id)
					WHERE g.soldto='".addslashes($user_id)."' AND YEAR(g.date)='".addslashes($year)."'
					ORDER BY g.date;";
				$year_result = mysql_query($year_command);
			
				if ($year_result != false) {
					echo "<h4>".$year." Season</h4>";
					echo "<table align='center'>\n";
					echo "<th width='50'>Date</th><th width='75'>Time</th><th width='200'>Team</th><th width='150'>Status</th>\n";
					while ($year_data = mysql_fetch_assoc($year_result)) {
						printf("<tr bgcolor='f0f0f0'><td align='center'><a href='game.php?gameID=%s'>%s</a></td><td align='center'>%s</td><td align='center'>%s %s</td><td align='center'>%s</td></tr>\n",
							$year_data[gameid], $year_data['date'], date('g:i a', strtotime($year_data['time'])), $year_data['city'], $year_data['name'], $year_data['status']);
					}

					echo "</table>\n";
				}
			}
		}
	}
}
?>

<span style='font-size:12px;color:red'><?php echo $error_message; ?></span>

