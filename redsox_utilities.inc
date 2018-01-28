<?php
require_once("secrets.inc");

function db_connect($host, $user, $pw, $database) {
	if (!($host && $user && $pw && $database)) {
		return false;
	} else {
		$db = mysql_connect($host, $user, $pw)
			or die("Unable to connect to mysql: ".mysql_error());

		mysql_select_db($database, $db)
			or die("Unable to connect to database '".$database."':".mysql_error());

		return $db;
	}
}

function login_db_connect() {
	$pw = get_password('redsox_login');
	return db_connect('localhost', 'redsox_login', $pw, 'redsox');
}

function member_db_connect() {
	$pw = get_password('redsox_member');
	return db_connect('localhost', 'redsox_member', $pw, 'redsox');
}

function public_db_connect() {
	$pw = get_password('redsox_public');
	return db_connect('localhost', 'redsox_public', $pw, 'redsox');
}

function admin_db_connect() {
	$pw = get_password('redsox_admin');
	return db_connect('localhost', 'redsox_admin', $pw, 'redsox');
}

function getMonthName($month) {
        return date("F", mktime(0, 0, 0, $month, 1, 2010));
}

function getDays($month) {
        if ($month < 8) {
                if ($month % 2 == 0) {
                        if ($month == 2) {
                                if (date("L", time()) == 1)
                                        return 29;
                                else
                                        return 28;
                        } else {
                                return 30;
                        }
                } else {
                        return 31;
                }
        } else {
                if ($month % 2 == 0) {
                        return 31;
                } else {
                        return 30;
                }
        }
}

function getImage($team) {
        $qry = "SELECT sm_url FROM teams WHERE id='".addslashes($team)."';";
        $result = mysql_query($qry);

        if ($result && mysql_num_rows($result) > 0) {
                $row = mysql_fetch_array($result);
                return $row['sm_url'];
        } else {
                return "bad.png";
        }
}

function fetch_profile($user_id, $db) {
	$profile_array = array();

	if (is_numeric($user_id)) {
		$command = "SELECT * FROM user_info WHERE user_id='".addslashes($user_id)."';";
		$result = mysql_query($command, $db);

		if ($result != false) {
			$profile_array = mysql_fetch_assoc($result);
		}
	}

	return $profile_array;
}

function fetch_profiles($db) {
	$profile_array = array();

	$command = "SELECT ui.user_id FROM user_info ui JOIN user_login li ON ui.user_id=li.user_id WHERE date(li.date_deleted)='0000-00-00' ORDER BY ui.name;";
	$result = mysql_query($command, $db);
	
	if ($result == false) {
		echo "Error retrieving user id data.";
	} else {
		while ($this_id = mysql_fetch_assoc($result)) {
			$this_profile = fetch_profile($this_id['user_id'], $db);
			if ($this_profile) {
				array_push ($profile_array, $this_profile);
			}
		}
	}

	return $profile_array;
}
	
function is_admin($user_id) {
	if (is_numeric($user_id)) {
		return $user_id == 46;
	} else {
		return false;
	}
}

function fetch_requested_games($user_id, $db) {
	$games_array = array();

	if (is_numeric($user_id)) {
		$command = "SELECT g.gameID, g.date, t.city, t.name, s.string as status_text FROM requests r
			JOIN games g ON (g.gameID=r.gameID) JOIN teams t ON (g.team_id=t.id) JOIN status s ON (s.id=g.status_id)
			WHERE r.user_id='".addslashes($user_id)."' AND date_deleted <= 0 AND g.date > now();";
		$result = mysql_query($command, $db);

		if ($result == false) {
			echo "Error fetching requested games: ".mysql_error();
		} else if (mysql_num_rows($result) > 0) {
			while ($this_game = mysql_fetch_assoc($result)) {
				array_push($games_array, $this_game);
			}
		}
	}
		
	return $games_array;
}

function fetch_game_requests($game_id, $db) {
        $games_array = array();

        if (is_numeric($game_id)) {
                $command = "SELECT g.gameID, g.date, t.city, t.name as team_name, s.string as status_text, ui.name as user_name, r.user_id, r.date_requested FROM requests r
                        JOIN games g ON (g.gameID=r.gameID) 
			JOIN teams t ON (g.team_id=t.id) 
			JOIN status s ON (s.id=g.status_id)
			JOIN user_info ui ON (ui.user_id=r.user_id)
                        WHERE r.gameID='".addslashes($game_id)."' AND date_deleted <= 0;";
                $result = mysql_query($command, $db);

                if ($result == false) {
                        echo "Error fetching game requests: ".mysql_error();
                } else if (mysql_num_rows($result) > 0) {
                        while ($this_game = mysql_fetch_assoc($result)) {
                                array_push($games_array, $this_game);
                        }
                }
        }

        return $games_array;
}

function is_requested($game_id, $user_id, $db) {
	$req_games = fetch_requested_games($user_id, $db);

	if (is_numeric($game_id)) {
		foreach($req_games as $this_game) {
			if ($this_game['gameID'] == $game_id) {
				return true;
			}
		}
	}
	
	return false;
}

function fetch_game($game_id, $db) {
	$this_game = array();

	if (is_numeric($game_id)) {
		$command = "SELECT g.gameID, g.date, g.time, t.city, t.name, s.string as status_text, g.soldto, g.status_id, 
			g.price, g.num_tickets, t.icon_url, t.sm_url
			FROM games g JOIN teams t ON (t.id = g.team_id) JOIN status s ON (s.id = g.status_id)
			WHERE g.gameID = '".addslashes($game_id)."';";
		$result = mysql_query($command, $db);

		if ($result != false) {
			$this_game = mysql_fetch_assoc($result);
		}
	}
	
	return $this_game;
}

function fetch_statuses($db) {
	$statuses_array = array();

	$command = "SELECT s.id, s.string as status_text FROM status s;";
	$result = mysql_query($command, $db);

	if ($result == false) {
		echo "Error retrieving status data: ".mysql_error();
	} else {
		while($this_status = mysql_fetch_assoc($result)) {
			array_push($statuses_array, $this_status);
		}
	}

	return $statuses_array;
}

function fetch_teams($db) {
        $teams_array = array();

        $command = "SELECT t.id, t.name, t.city FROM teams t WHERE t.city!='Boston';";
        $result = mysql_query($command, $db);

        if ($result == false) {
                echo "Error retrieving team data: ".mysql_error();
        } else {
                while ($this_team = mysql_fetch_assoc($result)) {
	                array_push($teams_array, $this_team);
		}
        }

        return $teams_array;
}

function print_games_table($games_array, $db) {
	if (count($games_array) > 0) {
		echo "<table align='center'>";
		echo "<th width='50'>Date</th><th width='75'>Time</th><th width='200'>Team</th><th width='200'>Status</th>";

		foreach ($games_array as $this_game) {
			$command = "SELECT g.gameID, g.date, g.time, t.city, t.name AS team_name, s.string AS status_text FROM games g
					JOIN teams t ON t.id = g.team_id
					JOIN status s ON s.id = g.status_id
					WHERE g.gameID = '".addslashes($this_game['gameID'])."';";
			$result = mysql_query($command);

			if ($result != false) {
				$game_data = mysql_fetch_assoc($result);
				printf("<tr><td align='center'><a href='game.php?gameID=%s'>%s</a></td><td align='center'>%s</td><td align='center'>%s</td><td align='center'>%s</td></tr>\n",
					$game_data['gameID'],
					$game_data['date'], 
					date('g:i a', strtotime($game_data['time'])),
					$game_data['city']." ".$game_data['team_name'],
					$game_data['status_text']);
			}
		}

		echo "</table>";
	}
}

function print_game_requests_table($game_requests) {
        //Now print a nice table
        echo "<table align='center'>";
        echo "<th align='center' width='50'>Date</th><th align='center' width='75'>Time</th><th align='center' width='200'>Team</th><th width='180'>Status</th><th align='center' width='200'>Requested By</th><th align='center' width='200'>Date Requested</th>\n";

        foreach($game_requests as $this_game) {
                $time = date('g:i a', strtotime($this_game['time']));
                $request_date = date('Y-m-d g:i a', strtotime($this_game['date_requested']));

                printf("<tr><td align='center'><a href='edit_game.php?gameID=%s'>%s</a></td><td align='center'>%s</td><td align='center'>%s %s</td><td align='center'>%s</td><td align='center'>%s</td><td align='center'>%s</td></tr>\n",
                        $this_game['gameID'],
                        $this_game['date'],
                        $time,
                        $this_game['city'],
                        $this_game['team_name'],
                        $this_game['status_text'],
                        $this_game['user_name'],
                        $request_date);
        }

        echo "</table>";
}

function mark_paid($game_id, $db) {
	if(is_numeric($game_id)) {
		$command = "UPDATE games SET status_id=1 WHERE gameid='".addslashes($game_id)."';";
		$result = mysql_query($command);

		if ($result == false) {
			echo "Error updating gameid=".$game_id.": ".mysql_error();
		}
	} else {
		echo "Game id is invalid: ".$game_id;
	}
}

function mark_reserved($game_id, $user_id, $db) {
        if(is_numeric($game_id) && is_numeric($user_id)) {
                $command = "UPDATE games SET status_id=2, soldto='".addslashes($user_id)."' WHERE gameid='".addslashes($game_id)."';";
                $result = mysql_query($command);

                if ($result == false) {
                        echo "Error updating gameid=".$game_id.": ".mysql_error();
                }
        } else {
                echo "Game id is invalid: ".$game_id;
        }
}	
?>
