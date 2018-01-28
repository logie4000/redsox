<?php
require_once("redsox_utilities.inc");

session_start();
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];

if (is_numeric($user_id)) {
	$db = login_db_connect();

	$command = "UPDATE user_login SET last_logout=now() WHERE user_id='".addslashes($user_id)."';";
	$result = mysql_query($command);

	if ($result == false) {
		$error_message = "Unable to logout. Please contact administrator.";
	} else {
		$_SESSION['user_id'] = '';
		$_SESSION['user_login'] = '';

		header("Location: index.php");
	}
}

?>

