<?php

$titles = array(0 => "", 1 => "Herr", 2 => "Frau");
$error = array("error" => true, "message" => "unknown error");
$entries = array();

$token = isset($_GET['token']) ? $_GET['token'] : FALSE;
if ($token)
{
	include '../../db/mysql.php';
	include '../../db/tables.php';
	
	$query = "SELECT * FROM ot_ticket_participant
	WHERE ot_ticket_participant.token = '$token'";
	$result = MySQL::query($query);
	if (MySQL::num_rows($result))
	{
		$ticket_participant = MySQL::fetch($result);
		$ticket_id = $ticket_participant["ticket_id"];
		$participant_id = $ticket_participant["id"];
		$participant_name = $titles[$ticket_participant["title"]]. " " . $ticket_participant["first_name"] . " " . $ticket_participant["last_name"];
		
		$query = "
		SELECT * FROM ot_ticket_entry, ot_ticket_participant, ot_ticket_entry_right
		WHERE ot_ticket_entry.ticket_id = $ticket_id
		AND ot_ticket_entry.participant_id = ot_ticket_participant.id
		AND ot_ticket_entry_right.entry_id = ot_ticket_entry.id
		AND ot_ticket_entry_right.participant_id = ot_ticket_participant.id
		ORDER BY ot_ticket_entry.id DESC
		";
		$result = MySQL::query($query);
		while ($ticket_entry = MySQL::fetch($result))
		{
			$entries[] = array (
					"type" => $ticket_entry["type"],
					"name" => $titles[$ticket_entry["title"]]." ".$ticket_entry["first_name"]." ".$ticket_entry["last_name"],
					"created" => date("d.m.Y H:i:s", $ticket_entry["timestamp_created"]),
					"text" => $ticket_entry["text"],
			);
		}
		
		$query = "
		UPDATE ot_ticket_entry_right
		SET `read` = 1
		WHERE ot_ticket_entry_right.participant_id = $participant_id
		";
		MySQL::query($query);
		
		$error["error"] = false;
		
	} else $error["message"] = "Invalid Token!";
} else $error["message"] = "Invalid Token!";

if ($error["error"])
{
	echo $error["message"];
	return;
}

?>
<!DOCTYPE html>
<html>
<head>
	<link type="text/css" media="all" rel="stylesheet" href="style.css" />
</head>
<body>
<div class="content_wrapper">
	<div>Hallo <?php echo $participant_name;?></div>
	<div class="ticket">
		<div class='ticket_head'>
			<div class='ticket_id'>Ticket #<?php echo $ticket_id; ?></div>
		</div>
		<div class='ticket_entry'>
			<input type="button" value="Antworten">
		</div>
		<?php 
		foreach ($entries as $entry)
		{
			switch ($entry["type"]) {
				case "0": $type_div = "<div class='type se'>Soforteinlösen</div>"; break;
				case "1": $type_div = "<div class='type user'>Kunde</div>"; break;
				case "2": $type_div = "<div class='type extern'>Partner</div>"; break;
				default: $type_div = "<div class='type'>Unknown</div>"; break;
			}
			
			echo "
			<div class='ticket_entry'>
			$type_div
			<div class='name'>$entry[name]</div>
			<div class='date'>$entry[created]</div>
			<div class='text'>$entry[text]</div>
			</div>
			";
		}
		?>
	</div>
</div>
</body>
</html>