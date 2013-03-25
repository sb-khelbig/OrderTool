<?php ob_start();

$json = array('error' => TRUE, 'errorMsg' => 'Unknown error');
$titles = array(0 => "", 1 => "Herr", 2 => "Frau");

try {
	$token = isset($_GET['token']) ? $_GET['token'] : FALSE;

	if ($token) {
		
		include '../db/mysql.php';
		include '../db/tables.php';
		
		$query = "SELECT * FROM ot_ticket_participant 
				WHERE ot_ticket_participant.token = '$token'";
		$result = MySQL::query($query);
		$ticket_participant = MySQL::fetch($result);
		$ticket_id = $ticket_participant["ticket_id"];
		$participant_id = $ticket_participant["id"];

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
			$json["data"][] = array (
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
		
		$json['error'] = false;
	} else {
		$json['errorMsg'] = "Invalid token!";
	}
	
} catch (Exception $e) {
	$json['error'] = TRUE;
	$json['errorMsg'] = "$e";
}

if ($errorMsg = ob_get_clean()) {
	$json['error'] = TRUE;
	$json['errorMsg'] = $errorMsg;
}

echo json_encode($json);
?>