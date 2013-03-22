<?php ob_start();

$json = array('error' => TRUE, 'errorMsg' => 'Unknown error');
$titles = array(0 => "Frau", 1 => "Herr");

try {
	$token = isset($_POST['token']) ? $_POST['token'] : FALSE;
	$text = isset($_POST['text']) ? $_POST['text'] : FALSE;

	if ($token) {
		if($text) {
			include '../db/mysql.php';
			include '../db/tables.php';
			
			$text = MySQL::escape($text);
			
			$query = "
			SELECT * FROM ot_ticket_participant WHERE token = '$token'
			";
			$result = MySQL::query($query);
			if (MySQL::num_rows($result))
			{
				$participant = MySQL::fetch($result);
				$ticket_id = $participant["ticket_id"];
				$participant_id = $participant["id"];
				$participant_type = $participant["type"];
				$timestamp_created = time();
				MySQL::start_transaction();
				$query = "
					INSERT INTO ot_ticket_entry 
					(ticket_id, participant_id, timestamp_created, text)
					VALUES
					('$ticket_id', '$participant_id', '$timestamp_created', '$text')
					";
				MySQL::query($query);
				$ticket_entry_id = MySQL::insert_id();
				$query = "
					INSERT INTO ot_ticket_entry_right
					(`entry_id`, `participant_id`, `read`)
					VALUES
					('$ticket_entry_id', '$participant_id', 1);
					";
				MySQL::query($query);
				
				$json["data"][] = array (
						"type" => $participant_type,
						"name" => $titles[$participant["title"]]." ".$participant["first_name"]." ".$participant["last_name"],
						"created" => date("d.m.Y H:i:s", $timestamp_created),
						"text" => $text,
				);
				
				MySQL::commit();
				$json['error'] = false;
			}
			else {
				$json['errorMsg'] = "Invalid token!";
			}
		} else {
			$json['errorMsg'] = "Kein Text!";
		}
	} else {
		$json['errorMsg'] = "No token!";
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