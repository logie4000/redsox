<?php

require("redsox_utilities.inc");

session_start();
$user_id = "";
if (array_key_exists('user_id', $_SESSION)) {
	$user_id = $_SESSION['user_id'];
}

$game_id = $_GET['gameID'];
$error_message = "";

if (is_numeric($game_id)) {
	if (is_numeric($user_id)) {
		$db = member_db_connect();
		$command = "SELECT g.date, g.time, t.city, t.name, t.sm_url, t.icon_url, t.abbrev, g.price, g.num_tickets, s.string as status, g.soldto 
			FROM games g 
			JOIN teams t ON (t.id = g.team_id) JOIN status s ON (s.id = g.status_id)
			WHERE g.gameid='".addslashes($game_id)."';";
	} else {
		$db = public_db_connect();
		$command = "SELECT g.date, g.time, t.city, t.name, t.sm_url, t.icon_url, t.abbrev, g.price, g.num_tickets FROM games g
			JOIN teams t ON (t.id = g.team_id) WHERE g.gameid='".addslashes($game_id)."';";
	}

	$result = mysql_query($command);
	if ($result == false) {
		$error_message = "Unable to retrieve game data for gameID=".$game_id.": ".mysql_error();
	} else if (mysql_num_rows($result) <= 0) {
		$error_message = "No game data found for gameID=".$game_id."(".$command."): ".mysql_error();
	} else {
		$bos_command = "SELECT * FROM teams WHERE id=30;";
		$bos_result = mysql_query($bos_command);
		$bos_data = mysql_fetch_assoc($bos_result);

		$game_data = mysql_fetch_assoc($result);
		$pageTitle = "Game " . addslashes($game_id) . " | " . $game_data['date'];
// Start HTML page
		include ("redsox_title.php");

		if (is_numeric($user_id)) {
			$dateGame = strtotime($game_data['date']." ".$game_data['time']);
			$date_diff = mktime() - $dateGame;

			if ($game_data['soldto'] == $user_id) {
				printf ("This is one of your games");
			} else if ($date_diff > 0) {
				printf ("This game has already been played.");
			} else if (is_requested($game_id, $user_id, $db)) {
				printf ("You have already requested this game. <a href='cancel_request.php?gameID=%s'>Cancel Request</a>",
						$game_id);
			} else if ($game_data['status'] == 'Available') {
				printf("<a href='request_game.php?gameID=%s'>Request Game</a>\n", $game_id);
			} else {
				printf("Game is not available for request\n");
			}
		} else {
			echo "<a href='login.php'>Log in</a> to request this game\n";
		}

		if (is_admin($user_id)) {
			printf(" | <a href='game_requests.php?gameID=%s'>Game Requests</a> | <a href='edit_game.php?gameID=%s'>Edit Game</a>", $game_id, $game_id);
		}

		printf("<br><a href='ical.php?gameID=%s'>Add to iCalendar</a>\n", $game_id);

?>
		<table align='center' border='0'>
		<tr><td colspan='2' align='center'><img src='<?php echo $bos_data['icon_url']; ?>'> vs. <img src='<?php echo $game_data['icon_url']; ?>'></td></tr>
		<tr><td align='right' width='50%'>Date:</td><td align='left' width='50%'><?php echo $game_data['date']; ?></td></tr>
		<tr><td align='right'>Time:</td><td align='left'><?php echo date('g:i a', strtotime($game_data['time'])); ?></td></tr>
		<tr><td align='right'>Team:</td><td align='left'><?php echo $game_data['city']." ".$game_data['name']; ?></td></tr>
		<tr><td align='right'>Ticket Price:</td><td align='left'><?php echo $game_data['price']; ?></td></tr>
		<tr><td align='right'>Status:</td><td align='left'><?php echo is_numeric($user_id) ? $game_data['status'] : "<a href='login.php'>Log in</a> to see status"; ?></td></tr>
		<?php
		printf("<tr><td align='center' colspan='2'><a href='http://mlb.mlb.com/mlb/gameday/index.jsp?gid=%s_%smlb_bosmlb_1'>MLB GameDay</a></td></tr>", date("Y_m_d", strtotime($game_data['date'])), $game_data['abbrev']);

		if (is_admin($user_id)) { 
			$profile_data = fetch_profile($game_data['soldto'], $db);
?>			<tr><td align='right'>Sold To:</td><td align='left'><?php echo "<a href='profile.php?profileID=".$profile_data['user_id']."'>".$profile_data['name']."</a>"; ?></td></tr>
			<tr><td align='right'>Number of Tickets:</td><td align='left'><?php echo $game_data['num_tickets']; ?></td></tr> <?php
		 } ?>
		</table>
<?php
	}
} else {
	$error_message = "Bad gameID value.";
}

echo $error_message;
?>

