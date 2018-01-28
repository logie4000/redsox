<?php

include("redsox_utilities.inc");

session_start();
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];

$game_id = $_GET['gameID'];

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

