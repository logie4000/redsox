<?php
require("ics.inc");
require("redsox_utilities.inc");

session_start();

$game_id = get_var('gameID');

if (is_numeric($game_id))
{
	$db = member_db_connect();
	$data = fetch_gameICS($game_id, $db);
	show("redsox_".$game_id, $data);
}
else
{
	echo "Invalid game id.";
}
?>
