<?php
require ("redsox_utilities.inc");

session_start();

if ($_POST && count($_POST) > 0) {
	// Verify the necessary data
	$login = $_POST['login'];
	$password = $_POST['password'];

	if (!($login && $password)) {
		$error_message = "Please be sure to provide all of the necessary fields.";
	} else {
		// Check the database
		$db = login_db_connect();
		$command = "SELECT user_id, login, password FROM user_login WHERE login='".addslashes($login)."'
				AND (password=password('".$password."') OR password=old_password('".$password."')) AND date_deleted<=0;";
		$result = mysql_query($command);

		if ($result == false) {
			$error_message = "Unable to query database.";
		} else if (mysql_num_rows($result) > 0) {
			//Success
			$data = mysql_fetch_object($result);
			$user_id = $data->user_id;

			//Update login date
			$command = "UPDATE user_login SET last_login=now(), last_logout='', password=password('".$password."') WHERE login='".addslashes($login)."' AND date_deleted<=0;";
			$result = mysql_query($command);
			
			if ($result == false) {
				$error_message = "Error updating user login table: ".mysql_error();
			} else {
				$_SESSION['user_id'] = $user_id;
				$_SESSION['user_login'] = $login;
				$referal = $_SESSION['referal'];
				if($referal) {
					$_SESSION['referal'] = "";
					header("Location: ".htmlentities($referal));
				} else {
					header("Location: index.php");
				}
			}
		} else {
			$error_message = "Login or password is incorrect.";
		}
	}
}

//Header
$pageTitle = "Login";
include("redsox_title.php");
?>

<span style='font-family:arial;font-size:12px;font-color:red;'>
<?php echo $error_message; ?>
</span>

<form action='login.php' method='post'>
<table align='center'>
<tr><td align='right' width='50%'>Login:</td><td align='left' width='50%'><input type='text' name='login' value='<?php echo $login; ?>' size='20' max='20'></td></tr>
<tr><td align='right'>Password:</td><td align='left'><input type='password' name='password' value='' size='20' max='20'></td></tr>
<tr><td colspan='2' align='right'><input type='submit' value='Submit'></td></tr>
</table>
</form>


</body></html>
			
