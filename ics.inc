<?php
function fetch_gameICS($game_id, $db) {

        if (is_numeric($game_id)) {
                $command = "SELECT g.gameID, g.date as date, g.time as time, t.city as city, t.name as name
                        FROM games g JOIN teams t ON (t.id = g.team_id)
                        WHERE g.gameID = '".addslashes($game_id)."';";

                $result = mysql_query($command, $db);
                if ($result == false) {
			echo "NO RESULT";
		} else if (mysql_num_rows($result) == 0) {
			echo "NO ROWS";
		} else {
                        $game_data = mysql_fetch_assoc($result);

                        $team = $game_data['city'] . " " . $game_data['name'];
			$startdate = $game_data['date'] . " " . $game_data['time'];
			$endtime = date("H:i", strtotime(sprintf("%s +3 hours", $game_data['time'])));
			$enddate = $game_data['date'] . " " . $endtime;

                        $data = "BEGIN:VCALENDAR\n";
			$data .= "PRODID:-//MLB.com//Schedule Calendar 0.001//EN\n";
			$data .= "VERSION:2.0\n";
			$data .= "CALSCALE:GREGORIAN\n";
			$data .= "METHOD:PUBLISH\n";
			$data .= "X-WR-CALNAME:\n";
			$data .= "X-WR-TIMEZONE:America/New_York\n";
			$data .= "BEGIN:VTIMEZONE\n";
			$data .= "TZID:America/New_York\n";
			$data .= "X-LIC-LOCATION:America/New_York\n";
			$data .= "BEGIN:DAYLIGHT\n";
			$data .= "TZOFFSETFROM:-0500\n";
			$data .= "TZOFFSETTO:-0400\n";
			$data .= "TZNAME:EDT\n";
			$data .= "DTSTART:19700308T020000\n";
			$data .= "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU\n";
			$data .= "END:DAYLIGHT\n";
			$data .= "BEGIN:STANDARD\n";
			$data .= "TZOFFSETFROM:-0400\n";
			$data .= "TZOFFSETTO:-0500\n";
			$data .= "TZNAME:EST\n";
			$data .= "DTSTART:19701101T020000\n";
			$data .= "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU\n";
			$data .= "END:STANDARD\n";
			$data .= "END:VTIMEZONE\n";
			$data .= "BEGIN:VEVENT\n";
			$data .= sprintf("UID=%d@teapothill.org\n", $game_id);
                        $data .= sprintf("DTSTART;TZID=America/New_York:%s\n", date("Ymd\THis\Z",strtotime($startdate)));
                        $data .= sprintf("DTEND;TZID=America/New_York:%s\n", date("Ymd\THis\Z",strtotime($enddate)));
                        $data .= "LOCATION:Fenway Park\nSEQUENCE:0\n";
                        $data .= sprintf("DTSTAMP:%s\n", date("Ymd\THis\Z"));
                        $data .= sprintf("SUMMARY:Red Sox vs. %s\n", $team);
                        $data .= sprintf("DESCRIPTION:Red Sox vs. %s at Fenway Park\n", $team);
                        $data .= "TRANSP:TRANSPARENT\n";
                        $data .= "END:VEVENT\nEND:VCALENDAR";

			return $data;
                }
        }
}

function show($game_id, $data) {
        header("Content-type:text/calendar");
        header('Content-Disposition: attachment; filename="'.$game_id.'.ics"');
        header('Content-Disposition: attachment; filename="'.$game_id.'.ics"');
        header('Content-Length: '.strlen($data));
        header('Connection: close');
        echo $data;
}
?>
