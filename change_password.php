<?php
require_once("redsox_utilities.inc");

include ("session_vars.inc");

if (!is_numeric($user_id)) {
	$error_message = "You must be logged in to change your password.";
} else if ($_POST && count($_POST) > 0) {
	$old_password = $_POST['old_password'];
	$password = $_POST['password'];
	$password2 = $_POST['password2'];

	if ($password != $password2) {
		$error_message = "Your new passwords did not match. Please try again.";
	} else if (strlen($password) < 5 || strlen($password) > 25) {
		$error_message = "Your new password must be between 5 and 25 characters in length.";
	} else {
		// Verify the old password
		$db = login_db_connect();
		$command = "SELECT * FROM user_login WHERE login='".addslashes($user_login)."' 
			AND password=password('".addslashes($old_password)."');";
		$result = mysql_query($command);

		if ($result == false) {
			$error_message = "Error validating current password: ".mysql_error();
		} else if (mysql_num_rows($result) <= 0) {
			$error_message = "Your existing password was incorrect: ".$command;
		} else {
			// Password matches the database
			$command = "UPDATE user_login SET password=password('".addslashes($password)."') 
				WHERE login='".addslashes($user_login)."';";
			$result = mysql_query($command);

			if ($result == false) {
				$error_message = "There was an error updating your password: ".mysql_error();
			} else {
				//Success
				$status_message = "You have sucessfully changed your password.";
			}
		}
	}
}


include("redsox_title.php");
if ($error_message) { 
?>
<span style='font-size:10px;color:red;'><?php echo $error_message; ?></span>
<?
} else if ($status_message) {
?>
<span style='font-size:10px;color:black;'><?php echo $status_message; ?></span>
<?
}
?>

<h4>Change Password for <?php echo $user_login; ?></h4>

<form action="change_password.php" method="post">
<table align='center'>
<tr><td align='right' width='50%'>Old Password:</td><td align='left' width='50%'><input type='password' name='old_password' value=''></td></tr>
<tr><td align='right'>New Password:</td><td align='left'><input type='password' name='password' value=''></td></tr>
<tr><td align='right'>Verify Password:</td><td align='left'><input type='password' name='password2' value=''></td></tr>
<tr><td colspan='2' align='right'><input type='submit' value='Submit'></td></tr>
</table>
</form>


	

