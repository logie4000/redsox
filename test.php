<?php
require("ics.inc");
require("redsox_utilities.inc");

session_start();

$game_id = "201501";
$db = member_db_connect();
$data = fetch_gameICS($game_id, $db);
show("redsox_".$game_id, $data);

?>
