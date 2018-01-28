<?php

require_once("redsox_utilities.inc");

session_start();
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];

$mygames_table = 'sold';
if ($_GET && $_GET['mode']) {
        $mygames_table = $_GET['mode'];
}

if ($_GET && is_numeric($_GET['year'])) {
        $year = $_GET['year'];
} else {
        $year = date('Y', time());
}

if ($_GET && is_numeric($_GET['profileID'])) {
        $profile_id = $_GET['profileID'];
} else {
	$profile_id = "";
}

include("redsox_title.php");

if (is_admin($user_id) && $profile_id != "") {
	$user_id = $profile_id;
}

if (is_numeric($user_id)) {
	$db = member_db_connect();
	$user_profile = fetch_profile($user_id, $db);
	
	$username = $user_profile['name'];
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
					echo "<h2>Requested Games for ".$username."</h2>\n";
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
		$years = array($year);
		if ($year == 0) {
			$year_command = "SELECT YEAR(g.date) as year FROM games g WHERE g.soldto='".addslashes($user_id)."'
				GROUP BY YEAR(g.date) ORDER BY g.date DESC;";


//      	        $command = "SELECT g.date, t.city, t.name, s.string as status FROM games g JOIN teams t ON (g.team_id = t.id)
//              	        JOIN status s ON (g.status_id = s.id) WHERE g.soldto = '".addslashes($user_id)."' ORDER BY g.date;";

			$year_result = mysql_query($year_command);
			if ($year_result == false) {
				$error_message = "Unable to retrieve game year data: ".mysql_error();
			} else if (mysql_num_rows($year_result) <= 0) {
				$error_message = "No games listed.";
			} else {
				while ($y = mysql_fetch_assoc($year_result)) {
					if ($year != $y['year']) {
						array_push($years, $y['year']);
					}
				}
			}
		}
		
		echo "<h2>Reserved Games for ".$username."</h2>";
		echo "<a href='mygames.php?mode=requests'>Show Game Requests</a><br>\n";

		// Retrieve the legal status values
		$status_command = "SELECT * FROM status ORDER BY id ASC;";
		$status_result = mysql_query($status_command);

		if ($status_result == false) {
				$error_message = "Error retrieving status data: ".mysql_error();
		} else if (mysql_num_rows($status_result) <= 0) {
				$error_message = "No status data.";
		} else {
			// Loop through each year
			foreach ($years as $year) {
				$yearly_total = 0;
				$status_result = mysql_query($status_command);
				while ($this_status = mysql_fetch_assoc($status_result)) {
												
					// Get price total
					$command = "SELECT SUM(g.price * g.num_tickets) AS total, COUNT(g.gameID) as count FROM games g
							WHERE g.status_id = '".addslashes($this_status['id'])."'
							AND YEAR(g.date) = '".addslashes($year)."' AND g.soldto = '".addslashes($user_id)."';";
						
					$total_result = mysql_query($command);

					if ($total_result == false) {
						echo "Error calculating total: ".mysql_error();
					} else {
						$total_data = mysql_fetch_assoc($total_result);
						$total = $total_data['total'];
						$count = $total_data['count'];

			//		echo "YEAR=".$year.", STATUS=".$this_status['id'].", TOTAL=".$total.", COUNT=".$count."<br>\n";
					}

					$game_command = "SELECT g.gameid, g.date, g.time, t.city, t.name, s.string as status_text, g.price FROM games g
								JOIN teams t ON (g.team_id = t.id) JOIN status s ON (g.status_id = s.id)
								WHERE g.status_id = '".addslashes($this_status['id'])."' 
								AND YEAR(g.date) = '".addslashes($year)."' AND g.soldto = '".addslashes($user_id)."' 
								ORDER BY g.date ASC;";

					$game_result = mysql_query($game_command);
					if ($game_result == false) {
						$error_message = "Unable to retrieve games data from query (".$game_command."): ".mysql_error();
						break;
					} else if (mysql_num_rows($game_result) <= 0) {
					//	$error_message = "No games data for ".$year;
					//	echo "No games: ".$game_command."<br>\n";
					} else {
						$yearly_total += $total;
						echo "<h4>".$year." Season</h4>";
						echo "<h4>".$this_status['string']." Total: $".$total." (".$count." games)</h4>";
						
						echo "<table align='center'>\n";
						echo "<th width='50'>Date</th><th width='75'>Time</th><th width='200'>Team</th><th width='100'>Price</th>\n";
						while ($game_data = mysql_fetch_assoc($game_result)) {
							printf("<tr bgcolor='d0d0d0'><td align='center'><a href='game.php?gameID=%s'>%s</a></td><td align='center'>%s</td><td align='center'>%s %s</td><td align='center'>%s</td></tr>\n",
								$game_data[gameid], $game_data['date'], date('g:i a', strtotime($game_data['time'])), $game_data['city'], $game_data['name'], $game_data['price']);
						}

						echo "</table>\n";
					}
				}

				if ($yearly_total == 0) {
					echo "No game data for ".$year."<br>\n";
				}
			}
		}
	}
}


if ($error_message != "") {
	echo $error_message;
}
