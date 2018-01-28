<?php
require_once("redsox_utilities.inc");

session_start();
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['user_login'];
$db = member_db_connect();

if (is_admin($user_id)) {
	if (array_key_exists('profileID', $_GET) && is_numeric($_GET['profileID'])) {
		$profile_id = $_GET['profileID'];
	} else {
		$profile_id = $user_id;
	}
} else {
	$profile_id = $user_id;
}


$profile = fetch_profile($profile_id, $db);
$pageTitle = "Profile | ".$profile['name'];
include ("redsox_title.php");
?>

<h2>Profile for <?php echo $profile['name']; ?></h2>
<?php if (is_admin($user_id)) {
	printf("<a href='edit_profile.php?profileID=%s'>Edit Profile</a> | ", $profile_id);
	
	if ($user_id == $profile_id) {	
		printf("<a href='change_password.php?profileID=%s'>Change Password</a> | ", $profile_id);
	}

	printf("<a href='by_status.php?profileID=%s'>%s</a>", $profile_id, $user_id == $profile_id ? "My Games" : "Games for ".$profile[name]);
} else {
        printf("<a href='edit_profile.php'>Edit Profile</a> | <a href='change_password.php'>Change Password</a> | <a href='mygames.php'>My Games</a>");
}

if (is_admin($user_id) && ($user_id == $profile_id)) {
	printf("<br><a href='by_status.php'>Games By Status</a> | <a href='by_user.php'>Games By User</a> | <a href='pending_requests.php'>Pending Requests</a> | <a href='users.php'>All Users</a>");
}

?>

<table align='center'>
<tr><td align='right' width='50%'>Name:</td><td align='left' width='50%'><?php echo $profile['name']; ?></td></tr>
<tr><td align='right'>Address 1:</td><td align='left'><?php echo $profile['address1']; ?></td></tr>
<tr><td align='right'>Address 2:</td><td align='left'><?php echo $profile['address2']; ?></td></tr>
<tr><td align='right'>City:</td><td align='left'><?php echo $profile['city']; ?></td></tr>
<tr><td align='right'>State:</td><td align='left'><?php echo $profile['state']; ?></td></tr>
<tr><td align='right'>Zip Code:</td><td align='left'><?php echo $profile['zip']; ?></td></tr>
<tr><td align='right'>Email Address:</td><td align='left'><?php echo $profile['email']; ?></td></tr>
</table>

