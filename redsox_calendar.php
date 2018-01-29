<?php
require_once("redsox_utilities.inc");

include ("session_vars.inc");

// Make a MySQL Connection
if (is_numeric($user_id)) {
	$con = member_db_connect();
} else {
	$con = public_db_connect();
}

?>
<html>
<head>
<title>Table Test</title>
<STYLE TYPE="text/css" MEDIA=screen>
<!--
p {font-family: "Arial", "Helvetica", "Sans Serif"; font-size: 12}
p.daytitle {font-variant: small-caps}
p.prevmonth {color: 888888}
p.nextmonth {color: 888888}

tr.daytitle {height: 2%; background-color: ffffff}
tr.daydata {font-family: "Arial", "Helvetica", "Sans Serif"; font-size: 12; vertical-align: top; height: 85}
tr.dhdaydata {font-family: "Arial", "Helvetica", "Sans Serif"; font-size: 12; vertical-align: top}

td.daytitle {font-family: "Arial", "Helvetica", "Sans Serif"; color:dd0000; font-size: 14; font-weight: bold; width: 125; text-align: center}
td.day1data {color: 000000; background-color: ddffee}
td.day2data {color: 000000; background-color: ddddee}
td.gamedata {color: 000000; background-color: ffffff}
td.dhgamedata {color: 000000; background-color: ffffff; font-size: 12}

td.prevmonth {height: 45; color: 999999; background-color: eeeeee}
td.nextmonth {height: 45; color: 999999; background-color: eeeeee}

tr {font-family: "Arial", "Helvetica", "Sans Serif"; font-size: 12}

.teamlogo {border: 0; align: center}
.availabledatetext {font-weight: bold; color: ff2020; font-size: 12}
.unavailabledatetext {font-weight: normal; color: ffbbbb; font-size: 12}
.dhavailabledatetext {font-weight: bold; color: ff2020; font-size: 12; horiz-align: center}
.dhunavailabledatetext {font-weight: bold; color: ffbbbb; font-size: 12}

-->
</STYLE>
</head>

<body bgcolor=d4d4d4>
<center>
<table border="0">
<tr class="daytitle">
<td class="daytitle"><p>Sunday</p></td>
<td class="daytitle"><p>Monday</p></td>
<td class="daytitle"><p>Tuesday</p></td>
<td class="daytitle"><p>Wednesday</p></td>
<td class="daytitle"><p>Thursday</p></td>
<td class="daytitle"><p>Friday</p></td>
<td class="daytitle"><p>Saturday</p></td>
</tr>
<?php
$dateNow = getDate();
$nowYear = $dateNow['year'];
$y = get_var('year');
if(!is_numeric($y)) {
	$y = "";
}

if (!$y) $y = $nowYear;


for ($m = 4;$m < 11; $m++) {
	$strMonthName = getMonthName($m);
	$strPrevMonthName = getMonthName($m - 1);
	$days = getDays($m);

	if ($days > 31)
		$days = 31;

	for ($d = 1; $d <= $days; $d++) {
		$strDate=sprintf("%04d%02d%02d", $y, $m, $d);
		$w = date('w', mktime(0, 0, 0, $m, $d, $y));

		if ($w == 0) {
			echo '<tr class="daydata">';
		} else if ($d == 1 && $m == 4) {
			for ($i = 1; $i <= $w; $i++) {
				if ($i == 1) {
					printf("<tr class=\"daydata\"><td class=\"prevmonth\"><p>%s  %d</p></td>", $strPrevMonthName, getDays($m - 1) - ($w - $i));
				} else {
					printf ("<td class=\"prevmonth\"><p>%d</p></td>", getDays($m - 1) - ($w - $i));
				}
			}
		}

		// Select game data from db or....
		if (is_numeric($user_id)) {
			$qry = "SELECT g.gameid, t.id as team_id, t.city, t.name, g.status_id, g.unid,
				 t.sm_url as sm_icon, s.string as status_text, g.soldto, g.time  
				FROM games g JOIN teams t ON (t.id=g.team_id) JOIN status s ON (s.id = g.status_id)
				WHERE date(date)='".addslashes($strDate)."' ORDER BY time ASC;";
		} else {
                        $qry = "SELECT g.gameid, t.id as team_id, t.city, t.name, g.status_id, g.unid, t.sm_url as sm_icon, g.time, '' as soldto
				FROM games g JOIN teams t ON (t.id=g.team_id) 
				WHERE date(date)='".addslashes($strDate)."' ORDER BY time ASC;";
		}

		$result = mysql_query($qry);
		$num_results = mysql_num_rows($result);

		if ($result == false) {
			echo mysql_error();
		} else if ($num_results > 0) {
			if ($d == 1) {
				printf ("<td class=\"gamedata\"><a name=\"%s\"><b>%s %d</b></a>", $strMonthName, $strMonthName, $d);
			} else {
				printf ("<td class=\"gamedata\"><b>%d</b>", $d);
			}

			$row = mysql_fetch_array($result);
			$image = getImage($row['team_id']);
			$status = $row['status_id'];
			$unid = $row['unid'];
			$isActive = 0;

			if ((($row['soldto'] && $row['soldto'] == $user_id) || is_admin($user_id)) && ($num_results == 1)) {
				$isActive = 1;
				printf("<p class='teamlogo'><center><a href='game.php?gameID=%s' target='_top'><img class='teamlogo' src='%s'></a><br>", $row['gameid'], $image);
			} else if ($status == 3 && $num_results == 1) {
				printf("<p class='teamlogo'><center><a href='game.php?gameID=%s' target='_top'><img class='teamlogo' src='%s'></a><br>", $row['gameid'], $image);
			} else {
				printf("<p class='teamlogo'><center><img class='teamlogo' src='%s'><br>", $image);
			}

			if ($num_results == 1) {
				printf("%s<br><span class='%savailabledatetext'>%s</span></center>", date('g:i a', strtotime($row['time'])), $status == 3 || $isActive == 1 ? "" : "un", $status == 3 ? "Available" : "Not Available");
			} else {
				$row2 = mysql_fetch_array($result);

				printf("\n<table><th width='50%%'>GAME 1</th><th width='50%%'>GAME 2</th>\n");
				printf("<tr class='dhdaydata'><td class='dhgamedata'>%s</td><td class='dhgamedata'>%s</td></tr>\n", date('g:i a', strtotime($row['time'])), date('g:i a', strtotime($row2['time'])));
				if (($row['soldto'] && $row['soldto'] == $user_id) || is_admin($user_id) || $row['status_id'] == 3)
					$isAvail1 = true;
				else
					$isAvail1 = false;

				if (($row2['soldto'] && $row2['soldto'] == $user_id) || is_admin($user_id) || $row2['status_id'] == 3)
					$isAvail2 = true;
				else
					$isAvail2 = false;

				printf("<tr class='dhdaydata'><td class='dhgamedata'><center><span class='dh%savailabledatetext'>%s%s%s</span></center></td><td class='dhgamedata'><center><span class='dh%savailabledatetext'>%s%s%s</span></center></td></tr>\n",
					$isAvail1 ? "" : "un",
					$isAvail1 ? "<a href='game.php?gameID=".$row['gameid']."' target='_top'>" : "",
					$row['status_id'] == 3 ? "Available" : "Not Available",
					$isAvail1 ? "</a>" : "",
					$isAvail2 ? "" : "un",
					$isAvail2 ? "<a href='game.php?gameID=".$row2['gameid']."' target='_top'>" : "",
					$row2['status_id'] == 3 ? "Available" : "Not Available",
					$isAvail2 ? "</a>" : "");

				printf("</table>\n");
			}

			printf("</td>");
		} else {
			// Print empty date
			if (($m % 2) == 0) {
				$strMonthClass = "day1data";
			} else {
				$strMonthClass = "day2data";
			}
		
			if ($d == 1) {
				printf("<td class=\"%s\"><a name=\"%s\"><b>%s %d</b></a>", $strMonthClass, $strMonthName, $strMonthName, $d);
			} else {
				printf("<td class=\"%s\"><b>%d</b>", $strMonthClass, $d);
			}
		}


		if ($w == 6) {
			echo "</tr>\n";
		}
	}
}

if ($w < 6) {
	for ($i = ($w + 1); $i <= 6; ++$i) {
		if (($i - $w) == 1) {
			printf ("<td class=\"nextmonth\"><p>%s %d</p></td>", getMonthName($m), $i - $w);
		} else {
			printf ("<td class=\"nextmonth\"><p>%d</p></td>", $i - $w);
		}
	}
}


?>
</tr>
</table>
</center>
</body>
</html>

