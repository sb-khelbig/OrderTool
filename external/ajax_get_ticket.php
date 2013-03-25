<?php ob_start();

$json = array('error' => TRUE, 'errorMsg' => 'Unknown error');
$titles = array(0 => "", 1 => "Herr", 2 => "Frau");

try {
	$token = isset($_GET['token']) ? $_GET['token'] : FALSE;

	if ($token) {
		
		include '../db/mysql.php';
		include '../db/tables.php';
		
		// TODO: join ot_ticket_entry_rights 
		$query = "
			SELECT * FROM ot_ticket_entry, ot_ticket_participant
			WHERE ot_ticket_entry.ticket_id = 
				(SELECT ticket_id FROM ot_ticket_participant 
				WHERE ot_ticket_participant.token = '$token')
			AND ot_ticket_entry.participant_id = ot_ticket_participant.id
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