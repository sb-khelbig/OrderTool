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
					);
			}
			
			$position_ticket_query = "
				SELECT ot_ticket.id, ot_ticket.timestamp_created, ot_ticket.status, article_name.data AS article_name
				FROM ot_value AS customer_value, ot_order, ot_position, ot_ticket_reference, ot_ticket, ot_value AS article_name
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
				GROUP BY ot_ticket.id
			";
			$result = MySQL::query($position_ticket_query);
			while ($ticket = MySQL::fetch($result))
			{
				$tickets[$ticket["id"]] = array(
					"created" => $ticket["timestamp_created"], 
					"status" =>  $ticket["status"],
					"text" => $ticket["article_name"],
					);
			}
			
			ksort($tickets);
			$tickets = array_reverse($tickets, true);
			
			foreach ($tickets as $ticket_id => $ticket_data)
			{
				$token_bin = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $ticket_id, MCRYPT_MODE_ECB , $iv);
				$token = base64_encode($token_bin);
				$tickets[$token]["created"] = date("d.m.Y", $ticket_data["created"]);
				$tickets[$token]["status"] = $status_codes[$ticket_data["status"]];
				$tickets[$token]["text"] = $ticket_data["text"];
				unset($tickets[$ticket_id]);
			}
			
			$json["data"] = $tickets;
			
			
			$json['error'] = false;
			
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