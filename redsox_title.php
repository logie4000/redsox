<?php
require_once ("redsox_utilities.inc");

include ("session_vars.inc");

$year = get_year();

$dateNow = getdate();
$thisYear = $dateNow['year'];
if ($year == "") $year = $thisYear;

$title = $year;
if ($pageTitle) {
	$title = $title . " | " . $pageTitle;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<title>Red Sox <?php echo $title; ?> </title>
<link rel="stylesheet" href="redsox_title.css" type="text/css">

</head>

<body>
<div id="title"><p class="title"><img src="images/sox_socks.png">BOSTON RED SOX <?php printf("%d SCHEDULE\n", $year); ?>
<img src="images/sox_socks.png"></p>
<?php
if ($user_login) {
	echo "<span class='plaintext'>Logged in as ".$user_login." | <a href='logout.php'>logout</a><br>\n";
	echo "<a href='index.php'>calendar</a> | <a href='profile.php'>profile</a></span>";
} else {
	echo "<span class='plaintext'><a href='login.php'>login</a> | <a href='register.php'>register</a></span>";
}
?>
</div><br>

</body>
</html>
