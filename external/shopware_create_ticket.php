<?php

ob_start();

$json = array('error' => TRUE, 'errorMsg' => 'Unknown error');

try {
	$token = isset($_POST['token']) ? $_POST['token'] : FALSE;
	$text = isset($_POST['text']) ? $_POST['text'] : FALSE;
	
	if ($token) {
		//decode token
		$data = json_decode($token);
		
		if (isset($data['data_source_id'])) {
			$data_source = DataSource::get($data['data_source_id']);
		}
		
		if (isset($data['cat_id'])) {
			$ticket_category = TicketCategory::get($data['cat_id']);
		}
		
		if (isset($data['ref_table'])) {
			$table = Table::get($data['ref_table']);
		}
		
		if ($table) {
			if (isset($data['ref_id'])) {
				$entity = $table::get($data['ref_id']);
				
				if ($text) {
					// TODO: speichern
				} else {
					$json['errorMsg'] = "Bitte geben Sie einen Text ein!";
				}
			}
		} else {
			$json['errorMsg'] = "Wrong reference!";
		}
	} else {
		$json['errorMsg'] = "Invalid token!";
	}
	
} catch (Exception $e) {
	$json['errorMsg'] = "$e";
}

if ($errorMsg = ob_get_clean()) {
	$json['errorMsg'] = $errorMsg;
}

echo json_encode($json); ?>