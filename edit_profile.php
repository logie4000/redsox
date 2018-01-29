<?php
require_once("redsox_utilities.inc");

include ("session_vars.inc");

if (is_numeric($user_id)) {
	$db = member_db_connect();

	if (is_admin($user_id)) {
		$isadmin = true;
		$profile_id = get_var('profileID');
		if (!is_numeric($profile_id)) {
			$profile_id = $user_id;
		}
	} else {
		$isadmin = false;
		$profile_id = $user_id;
	}

	$profile_array = fetch_profile($profile_id, $db);


	$name = $profile_array['name'];
	$address1 = $profile_array['address1'];
	$address2 = $profile_array['address2'];
	$city = $profile_array['city'];
	$state = $profile_array['state'];
	$zip = $profile_array['zip'];
	$email = $profile_array['email'];

	// Handle changes to profile here...
	if ($_POST && count($_POST) > 0) {
		$name = $_POST['name'];
		$address1 = $_POST['address1'];
		$address2 = $_POST['address2'];
		$city = $_POST['city'];
		$state = $_POST['state'];
		$zip = $_POST['zip'];
		$email = $_POST['email'];

		//Check required fields
		if (!($name && $address1 && city && $state && $zip && $email) && !$isadmin) {
			$error_message = "Please include all required fields.";
		} else if (strlen($name) > 50) {
			$error_message = "Please limit the name field to 50 characters or less.";
		} else if ((strlen($address1) > 50) || ($address2 && (strlen($address2) > 50))) {
			$error_message = "Please limit the address fields to 50 characters or less.";
		} else if (strlen($city) > 50) {
			$error_message = "Please limit the city field to 50 characters or less.";
		} else if (strlen($zip) > 15) {
			$error_message = "Please limit the zip code field to 15 characters or less.";
		} else if (strlen($email) > 100) {
			$error_message = "Please limit the email fields to 100 characters or less.";
		} else {
			// Process the data...
			// First check for the existence of the user id
			$success = true;
			$command = "SELECT user_id, login FROM user_login WHERE user_id='".addslashes($profile_id)."';";
			$result = mysql_query($command);
		
			if ($result == false) {
				$error_message = "There was an error validating the user ID: ".mysql_error();
				$success = false;
			} else if (mysql_num_rows($result) <= 0) {
				$error_message = "There was an error retrieving the user data: No user id found";
				$success = false;
			}

			if ($success) {
				// Check the state value
				$command = "SELECT * FROM states WHERE abbrev='".addslashes($state)."';";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "There was an error validating the state value: ".mysql_error();
					$success = false;
				} else if (mysql_num_rows($result) <= 0) {
					$error_message = "The state value is invalid: ".$command;
					$success = false;
				}
			}
	
			if ($success) {
				// Update the user data...
				$command = "UPDATE user_info SET name='".addslashes($name)."', address1='".addslashes($address1)."', 
						address2='".addslashes($address2)."', city='".addslashes($city)."', 
						state='".addslashes($state)."', zip='".addslashes($zip)."', 
						email='".addslashes($email)."' WHERE user_id='".addslashes($profile_id)."';";
				$result = mysql_query($command);

				if ($result == false) {
					$error_message = "There was an error updating the user info table: ".mysql_error();
					$success = false;
				} else {
					header("Location: profile.php?profileID=".$profile_id);
				}
			}
		}
	}
}

include ("redsox_title.php");
?>
<h2>Edit Profile</h2>
<span style='font-size:12px;font-color:red;'>
<?php echo $error_message; ?>
</span>

<form action='edit_profile.php?profileID=<?php echo $profile_id; ?>' method='post'>
<table align='center'>
<tr><td align='right' size='50%'>Name:</td><td align='left' size='50%'><input type='text' name='name' value='<?php echo $name; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>Address 1:</td><td align='left'><input type='text' name='address1' value='<?php echo $address1; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>Address 2:</td><td align='left'><input type='text' name='address2' value='<?php echo $address2; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>City:</td><td align='left'><input type='text' name='city' value='<?php echo $city; ?>' max='50' size='30'></td></tr>
<tr><td align='right'>State:</td><td align='left'><select name='state'>
<?
	echo "<option value=''".($state == '' ? ' selected=\'selected\'' : '').">Select a state</option>\n";

	$command = "SELECT abbrev, name FROM states;";
	$result = mysql_query($command);

	if ($result != false) {
		while ($data = mysql_fetch_object($result)) {
			printf("<option value='%s'%s>%s</option>\n", $data->abbrev, $data->abbrev == $state ? " selected='selected'" : "", $data->name);
		}
	}
?>
</select></td></tr>
<tr><td align='right'>Zip Code:</td><td align='left'><input type='text' name='zip' value='<?php echo $zip; ?>' max=15 size=15></td></tr>
<tr><td align='right'>Email Address:</td><td align='left'><input type='text' name='email' value='<?php echo $email; ?>' max=100 size='30'></td></tr>
<tr><td colspan='2' align='right'><input type='submit' value='Save'></td></tr>
</table>
<input type='hidden' name='userID' value='<?php echo $user_id; ?>'>
</form>

