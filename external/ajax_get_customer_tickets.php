<?php ob_start();

$json = array('error' => TRUE, 'errorMsg' => 'Unknown error');#
$status_codes = array(0 => 'Offen', 1 => 'In Bearbeitung', 2 => 'Beantwortet', 3 => 'Geschlossen');

$key = "CsqH5#`Ve/v`?v9T^dQ0ypcw@avsZHhn";

$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB );
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

try {
	$token = isset($_GET['token']) ? $_GET['token'] : FALSE;

	if ($token) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
		$decrypted_string = rtrim(mcrypt_decrypt(MCRYPT_BLOWFISH, $key, base64_decode($token), MCRYPT_MODE_ECB , $iv), "\0");
		
		if ($data = json_decode($decrypted_string, true)) {
			include '../db/mysql.php';
			include '../db/tables.php';
			
			$data_source_id = $data["data_source_id"];
			$customer_id = $data["customer_id"];
			
			$tickets = array();
			
			$order_ticket_query = "
				SELECT ot_ticket.id, ot_ticket.timestamp_created, ot_ticket.status
				FROM ot_value AS customer_value, ot_order, ot_ticket_reference, ot_ticket
				WHERE customer_value.attribute_id = 23
				AND   customer_value.data = $customer_id
				AND   customer_value.ref_id = ot_order.customer_id
				AND   ot_order.data_source_id = $data_source_id
				AND   ot_order.id = ot_ticket_reference.ref_id
				AND   ot_ticket.id = ot_ticket_reference.ticket_id
				AND   ot_ticket.ref_table = 'ot_order'
			";
			$result = MySQL::query($order_ticket_query);
			while ($ticket = MySQL::fetch($result))
			{
				$tickets[$ticket["id"]] = array(
					"created" => $ticket["timestamp_created"], 
					"status" =>  $ticket["status"],
					"text" => "Bestellung",
					"ticket_id" => $ticket["id"],
					);
			}
			
			$position_ticket_query = "
				SELECT ot_ticket.id, ot_ticket.timestamp_created, ot_ticket.status, article_name.data AS article_name, min(entry_right.read) as 'read'
				FROM ot_value AS customer_value, ot_order, ot_position, ot_ticket_reference, ot_ticket, ot_value AS article_name, ot_ticket_entry_right AS entry_right, ot_ticket_entry AS entry
				WHERE customer_value.attribute_id = 23
				AND   customer_value.data = $customer_id
				AND   customer_value.ref_id = ot_order.customer_id
				AND   article_name.attribute_id = 21
				AND   article_name.ref_id = ot_position.id
				AND   ot_order.data_source_id = $data_source_id
				AND   ot_position.id = ot_ticket_reference.ref_id
				AND   ot_ticket.id = ot_ticket_reference.ticket_id
				AND   ot_position.order_id = ot_order.id
				AND   ot_ticket.ref_table = 'ot_position'
				AND   entry.ticket_id = ot_ticket.id
				AND   entry_right.entry_id = entry.id
				GROUP BY ot_ticket.id
				ORDER BY max(entry.id) DESC
			";
			$result = MySQL::query($position_ticket_query);
			while ($ticket = MySQL::fetch($result))
			{
				$tickets[$ticket["id"]] = array(
					"created" => $ticket["timestamp_created"], 
					"status" =>  $ticket["status"],
					"text" => $ticket["article_name"],
					"ticket_id" => $ticket["id"],
					"read" => $ticket["read"],
					);
			}
			
			if (count($tickets) > 0) {
				$query = "
					SELECT token, ticket_id 
					FROM ot_ticket_participant
					WHERE ticket_id IN (".join(',',array_keys($tickets)).")
					AND type = 1
				";
				$result = MySQL::query($query);
				while ($ticket = MySQL::fetch($result))
				{
					$tickets[$ticket["ticket_id"]]["created"] = date("d.m.Y", $tickets[$ticket["ticket_id"]]["created"]);
					$tickets[$ticket["ticket_id"]]["status"] = $status_codes[$tickets[$ticket["ticket_id"]]["status"]];
					$tickets[$ticket["ticket_id"]]["token"] = $ticket["token"];
				}
				
				$json["data"] = $tickets;
				$json['error'] = false;
			} else
			{
				$json['errorMsg'] = "Keine Tickets gefunden!";
			}
		}
		else {
			$json['errorMsg'] = "Invalid token!";
		}
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