<?php
$dateNow = getDate();
$thisYear = $dateNow['year'];
$year = $_GET['year'];
if ($year == "") $year = $thisYear;

echo ("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\">\n");
echo "<html>\n";
echo "<head>\n";
echo "<title>Red Sox Tickets</title>\n";

echo "<link rel=\"stylesheet\" href=\"redsox.css\" type=\"text/css\">\n";

echo "</head>\n";
echo "<body>\n";

echo "<div id=\"title\">\n";
printf ("<iframe id=\"frTitle\" name=\"frTitle\" scrolling=\"no\" width=\"100%%\" height=\"100%%\" frameborder=\"0\" src=\"redsox_title.php?year=%d\">\n", $year);
echo "</iframe>\n";
echo "</div>\n\n";

echo "<div id=\"content\">\n";
printf ("<iframe scrolling=\"auto\" frameborder=\"0\" id=\"frcontent\" width=\"100%%\" height=\"100%%\" name=\"frcontent\" src=\"redsox_calendar.php?year=%d\"></iframe>\n", $year);
echo "</div>\n\n";

echo "</body>\n";
echo "</html>\n";
