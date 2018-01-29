<?php
require_once("redsox_utilities.inc");

include ("session_vars.inc");


if ($_POST && count($_POST) > 0) {
	//Validate input
	$name = $_POST['name'];
	$address1 = $_POST['address1'];
	$address2 = $_POST['address2'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	$password = $_POST['password'];
	$password2 = $_POST['password2'];
	$login = $_POST['login'];
	$email = $_POST['email'];

	if (!($name && $address1 && $city && $state && $zip && $password && $password2 && $login && $email)) {
		$error_message = "Please be sure to provide values for all fields.";
	} else if (strlen($name) > 50) {
		$error_message = "Name value is invalid.";
	} else if (strlen($address1) > 50) {
		$error_message = "Address line 1 is invalid.";
	} else if (address2 && strlen($address2) > 50) {
		$error_message = "Address line 2 is invalid.";
	} else if (strlen($city) > 50) {
		$error_message = "City is an invalid.";
	} else if (strlen($state) > 2) {
		$error_message = "State is invalid.";
	} else if (strlen($zip) > 15 || strlen($zip) < 5) {
		$error_message = "Zip code is invalid.";
	} else if (strlen($email) > 100) {
		$error_message = "Email is invalid.";
	} else if (strlen($login) > 50) {
		$error_message = "Login is invalid.";
	} else if (strlen($password) > 25) {
		$error_message = "Password is invalid.";
	} else if ($password != $password2) {
		$error_message = "Passwords do not match.";
	} else {
		//Check db constraints
		$success = true;
		$db = member_db_connect();
		$command = "SELECT * FROM user_info WHERE name='".addslashes($name)."' AND address1='".addslashes($address1)."' 
				AND address2='".addslashes($address2)."' AND city='".addslashes($city)."' AND state='".addslashes($state)."';";
		$result = mysql_query($command);

		if ($result == false) {
			$error_message = "Error validating user and address data: ".mysql_error();
			$success = false;
		} else if (mysql_num_rows($result) > 0) {
			$error_message = "A user with that name and address already exists.";
			$success = false;
		}

		if ($success) {
			$command = "SELECT * FROM states WHERE abbrev='".addslashes($state)."';";
			$result = mysql_query($command);

			if ($result == false) {
				$error_message = "Error validating state value: ".mysql_error();
				$success = false;
			} else if (mysql_num_rows($result) <= 0) {
				$error_message = "State value is not a valid abbreviation.";
				$success = false;
			}
		}
		
		if ($success) {
			$db = login_db_connect();
			$command = "SELECT * FROM user_login WHERE login='".addslashes($login)."';";
			$result = mysql_query($command);

			if ($result == false) {
				$error_message = "Error validating login value: ".mysql_error();
				$success = false;
			} else if (mysql_num_rows($result) > 0) {
				$error_message = "There is already a user with that login in the database.";
				$success = false;
			}
		}

		if ($success) {
			//Update tables
			mysql_query("SET AUTOCOMMIT=0");
			$result = mysql_query("BEGIN");
			
			if ($result == false) {
				$error_message = "Unable to begin transaction: ".mysql_error();
				$success = false;
			}
			
			if ($success) {
				//Update the user_login table
				$command = "INSERT INTO user_login (user_id, login, password) 
						VALUES('', '".addslashes($login)."', password('".addslashes($password)."'));";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "Error adding user data to login table: ".mysql_error();
					$success = false;
				} else if (mysql_affected_rows() <= 0) {
					$error_message = "No login added (".$command."): ".mysql_error();
					$success = false;
				} else {
					$user_id = mysql_insert_id();
				}
			}

			if ($success) {
				$command = "INSERT INTO user_info (user_id, name, email)
					VALUES ('".addslashes($user_id)."',
					'".addslashes($name)."',
					'".addslashes($email)."');";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "Error adding user data to info table (".$command."): ".mysql_error();
					$success = false;
				} else if (mysql_affected_rows() <= 0) {
					$error_message = "No info added (".$command."): ".mysql_error();
					$success = false;
				}
			}

			if ($success) {
				// Commit the changes so far because the db connection will change
				mysql_query("COMMIT");
			
				// Now update the rest of user_info
				$db = member_db_connect();
				$command = "UPDATE user_info SET address1='".addslashes($address1)."',
					address2='".addslashes($address2)."',
					city='".addslashes($city)."',
					state='".addslashes($state)."',
					zip='".addslashes($zip)."'
					WHERE user_id='".$user_id."';";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "Error updating user record: ".mysql_error();
					$success = false;
				} else if (mysql_affected_rows() <= 0) {
					$error_message = "No info updated (".$command."): ".mysql_error();
					$success = false;
				}
			}
		}	

		if ($success) {
			mysql_query("COMMIT");
		} else {
			mysql_query("ROLLBACK");
		}

		mysql_query("SET AUTOCOMMIT=1");
			
		//Redirect user
		if ($success) {
			$_SESSION['user_id'] = $user_id;
			$_SESSION['user_login'] = $login;
			header("Location: profile.php");
		}
	}

}

include("redsox_title.php");

if ($error_message) {
	echo "<span style='font-size:12px;color:red;'>".$error_message."</span>";
}

?>
<form action="register.php" method="post">
<table align='center'>
<tr><td align='right' width='50%'>Name:</td><td align='left' width='50%'><input type='text' name='name' value='<?php echo $name; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>Address Line 1:</td><td align='left'><input type='text' name='address1' value='<?php echo $address1; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>Address Line 2:</td><td align='left'><input type='text' name='address2' value='<?php echo $address2; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>City:</td><td align='left'><input type='text' name='city' value='<?php echo $city; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>State:</td><td align='left'>
	<select name='state'>
	<option value=''>Select a state...</option>
<?php	$db = login_db_connect();
	$command = "SELECT * FROM states;";
	$result = mysql_query($command);

	if ($result == false) {
		echo "Unable to retrieve state data: ".mysql_error();
	} else {
		while ($state_data = mysql_fetch_assoc($result)) {
			printf("<option value='%s'%s>%s</option>", 
				$state_data['abbrev'], 
				$state_data['abbrev'] == $state ? " selected='selected'" : "",
				$state_data['name']);
		}
	} ?>
	</select>
<tr><td align='right'>Zip Code:</td><td align='left'><input type='text' name='zip' value='<?php echo $zip; ?>' max='15' size='15'></td></tr>
<tr><td align='right'>Email:</td><td align='left'><input type='text' name='email' value='<?php echo $email; ?>' max='100' size='30'></td></tr>
</table>
<br><br>
<table align='center'>
<tr><td align='right' width='50%'>Login:</td><td align='left' width='50%'><input type='text' name='login' value='<?php echo $login; ?>' max='25' size='25'></td></tr>
<tr><td align='right'>Password:</td><td align='left'><input type='password' name='password' max='25' size='25'></td></tr>
<tr><td align='right'>Verify Password:</td><td align='left'><input type='password' name='password2' max='25' size='25'></td></tr>
<tr><td align='right' colspan='2'><input type='submit' value='Submit'></td></tr>
</table>


