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
$pageTitle = "All Users";
include("redsox_title.php");

if (!is_admin($user_id)) {
	echo "Only administrators may see the results by user.";
} else {
	$db = admin_db_connect();


	$command = "SELECT ul.user_id id, ul.login, ui.name, ui.email FROM user_login ul JOIN user_info ui ON ui.user_id = ul.user_id ORDER BY login ASC;";
	$users_result = mysql_query($command);

	if ($users_result == false) {
		$error_message = "Error retrieving users data: ".mysql_error();
	} else if (mysql_num_rows($users_result) > 0) {
				echo "<table align='center'>\n";
				echo "<tr><th width='200'>Login</th><th width='250'>Name</th><th width='150'>Email</th><th width 75>ID</th><th width='75'>Edit</th></tr>\n";
		while ($this_user = mysql_fetch_assoc($users_result)) {
						printf("<tr><td align='center'>%s</a></td><td align='center'>%s</td><td align='center'>%s</td><td align='center'>%s</td><td align='center'><form method='GET' action='edit_profile.php'><input type='hidden' name='profileID' value='%s'>%s</form></td></tr>\n", 
							$this_user['login'], 
							$this_user['name'], 
							$this_user['email'],
							$this_user['id'],
							$this_user['id'],
							"<input type='submit' value='Edit'>");
		}

		echo "</table>\n";

	} else {
		echo "No users found.";
	}
}

if ($error_message) {
	echo $error_message;
}
?>

				


