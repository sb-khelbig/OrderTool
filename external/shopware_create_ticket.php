<?php

ob_start();

$json = array('error' => TRUE, 'errorMsg' => 'Unknown error');

try {
	$token = isset($_POST['token']) ? $_POST['token'] : FALSE;
	$text = isset($_POST['text']) ? trim($_POST['text']) : FALSE;
	
	if ($token) {
		//decode token
		$key = "CsqH5#`Ve/v`?v9T^dQ0ypcw@avsZHhn";
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted_string = base64_decode($token);
		$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $encrypted_string, MCRYPT_MODE_ECB , $iv);
		$decrypted_string = rtrim($decrypted_string, "\0");
		$data = json_decode($decrypted_string);
		
		if ($text) {
			include '../db/mysql.php';
			include '../db/tables.php';
			
			if (isset($data['data_source_id'])) {
				$data_source = DataSource::get($data['data_source_id']);
					
				if (isset($data['cat_id'])) {
					$ticket_category = TicketCategory::get($data['cat_id']);
			
					if (isset($data['ref_table'])) {
							
						if ($table = Table::get($data['ref_table'])) {
			
							if (isset($data['ref_id'])) {
									
								if ($table == 'Order') {
									$attribute = Attribute::get(3);
								} elseif ($table == 'Position') {
									$attribute = Attribute::get(15);
								} else {
									throw new Exception('Unsupported reference!');
								}
								
								$values = Value::filter(array(
										'attribute_id' => $attribute->id,
										'data' => MySQL::escape($data['ref_id']),
									)
								);
								
								if ($values) {
									$ids = array();
									foreach ($values as $value) {
										$ids[] = $value->ref_id;
									}
									
									if ($table == 'Order') {
										$orders = Order::filter(array(
												'id' => $ids,
												'data_source_id' => $data_source->id,
											)
										);
										
										if (count($orders) == 1) {
											$order = array_pop($orders);
											
											$ticket = new Ticket();
											$ticket->category = $ticket_category;
											$ticket->ref_table = $table::getTableName();
											$ticket->ref_id = $order->id;
										} else {
											$json['errorMsg'] = "ID not found!";
										}
									} elseif ($table == 'Position') {
										$query = "	SELECT p.id
													FROM ot_position AS p, ot_order AS o
													WHERE p.order_id = o.id
														AND o.data_source_id = " . $data_source->id . "
														AND p.id IN (" . join(', ', $ids) . ")";
										
										if ($result = MySQL::query($query)) {
											if (MySQL::num_rows($result) == 1) {
												$position = MySQL::fetch($result);
												$position = Position::get($position['id']);
											} else {
												$json['errorMsg'] = "ID not found!";
											}
										}
									}
								} else {
									$json['errorMsg'] = "ID not found!";
								}
							} else {
								$json['errorMsg'] = "ID not set!";
							}
						} else {
							$json['errorMsg'] = "Invalid reference!";
						}
					} else {
						$json['errorMsg'] = "Reference not set!";
					}
				} else {
					$json['errorMsg'] = "Category not set!";
				}
			} else {
				$json['errorMsg'] = "DataSource not set!";
			}
		} else {
			$json['errorMsg'] = "Bitte geben Sie einen Text ein!";
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