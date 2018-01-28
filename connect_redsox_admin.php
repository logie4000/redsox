<?php

$host = "localhost";
$user = "redsoxadmin";
$pw = "passw0rd";
$database = "redsox";

$db = mysql_connect($host, $user, $pw)
	or die ("Unable to connect to MySQL: ".mysql_error());

mysql_select_db($database, $db)
	or die ("Unable to connect to database: ".$database." (".mysql_error().")");

