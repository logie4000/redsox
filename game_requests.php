<?php

include("redsox_utilities.inc");

include ("session_vars.inc");

$game_id = get_var('gameID');

if (!is_admin($user_id)) {
	echo "Only administrators are allowed to view game requests.";
} else if (is_numeric($game_id)) {
	include ("redsox_title.php");
	$db = admin_db_connect();
	$game_requests = fetch_game_requests($game_id, $db);

	//Now print a nice table
	print_game_requests_table($game_requests);
	
} else {
	echo "Bad gameID value.";
}

