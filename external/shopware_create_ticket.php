<?php ob_start();

$json = array('error' => TRUE, 'errorMsg' => 'Unknown error');

$key = "CsqH5#`Ve/v`?v9T^dQ0ypcw@avsZHhn";

try {
	$token = isset($_POST['token']) ? $_POST['token'] : FALSE;
	$text = isset($_POST['text']) ? trim($_POST['text']) : FALSE;
	
	if ($token) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	
		$decrypted_string = rtrim(mcrypt_decrypt(MCRYPT_BLOWFISH, $key, base64_decode($token), MCRYPT_MODE_ECB , $iv), "\0");
		
		if ($data = json_decode($decrypted_string, TRUE)) {
		
			if ($text) {
				$text = nl2br($text);
				
				include __DIR__ . '/../db/mysql.php';
				include __DIR__ . '/../db/tables.php';
				
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
										$attribute = Attribute::get(14);
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
											$ids[] = $value->reference;
										}
										
										include __DIR__ . '/save_ticket_function.php';
										
										if ($table == 'Order') {
											$orders = Order::filter(array(
													'id' => $ids,
													'data_source_id' => $data_source->id,
												)
											);
											
											if (count($orders) == 1) {
												$json['data']['ticket_id'] = save_ticket(
														$ticket_category, $table, $orders, $text, array(
														'reference' => $orders[0]->customer->id,
														'title' => 27,
														'first_name' => 28,
														'last_name' => 29,
														'mail' => 25,
													)
												);
												
												$json['error'] = FALSE;
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
												if (MySQL::num_rows($result) > 0) {
													$ids = array();
													while ($row = MySQL::fetch($result)) {
														$ids[] = $row['id'];
													}
													$positions = Position::filter(array('id' => $ids));
													
													$json['data']['ticket_id'] = save_ticket(
															$ticket_category, $table, $positions, $text, array(
															'reference' => $positions[0]->order->customer->id,
															'title' => 27,
															'first_name' => 28,
															'last_name' => 29,
															'mail' => 25,
														)
													);
													
													$json['error'] = FALSE;
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

echo json_encode($json); ?>