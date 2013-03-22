<?php ob_start();

include '../db/mysql.php'; include '../db/tables.php';

$json = array('error' => TRUE, 'errorMsg' => 'Unbekannter Fehler');

switch ($_GET['action']) {
	case 'load':
		$order = Order::get($_GET['id']);
		
		// Attributes
		$order_atrs = $order->attributes->all();
		$name = 'Attribute';
		$id = 'attributes_' . $order->id;
		$content = array();
		if ($order_atrs) {
			$content[] = '<ul>';
			foreach ($order_atrs as $atr) {
				$content[] = '<li>';
					$content[] = $atr->attribute->name . ': ' . $atr->data;
				$content[] = '</li>';
			}
			$content[] = '</ul>';
		} else {
			$content[] = '<p>Keine Attribute vorhanden</p>';
		}
		$data['attributes'] = array('name' => $name, 'content' => join('', $content), 'id' => $id);
		
		// Customer
		$customer = $order->customer;
		$name = 'Kunde';
		$id = 'customer_' . $order->id;
		$content = array();
		if ($customer) {
			$customer_atrs = $customer->attributes->all();
			if ($customer_atrs) {
				$content[] = '<ul>';
				foreach ($customer_atrs as $atr) {
					$content[] = '<li>';
						if ($atr->attribute->name == 'Vorname') {
							$ticket_data['inquirer_first_name'] = $atr->data;
						} elseif ($atr->attribute->name == 'Nachname') {
							$ticket_data['inquirer_last_name'] = $atr->data;
						} elseif ($atr->attribute->name == 'E-Mail Adresse') {
							$ticket_data['inquirer_mail'] = $atr->data;
						} elseif ($atr->attribute->name == 'Anrede') {
							$ticket_data['inquirer_title'] = ($atr->data == 'ms') ? 2 : 1;
						}
						$content[] = $atr->attribute->name . ': ' . $atr->data;
					$content[] = '</li>';
				}
				$content[] = '</ul>';
			} else {
				$content[] = '<p>Keine Attribute vorhanden</p>';
			}
		} else {
			$content[] = '<p>Kein Kunde vorhanden</p>';
		}
		$data['customer'] = array('name' => $name, 'content' => join('', $content), 'id' => $id);
		
		// Positions
		$positions = $order->positions->all();
		$name = 'Positionen (' . count($positions) . ')';
		$id = 'positions_' . $order->id;
		$content = array();
		if ($objects = $positions) {
			$attributes = array('ID' => 'ID');
			$rows = array();
			foreach ($objects as $entity) {
				$rows[$entity->id]['ID'] = $entity->id;
				foreach ($entity->attributes->all() as $atr) {
					if (!array_key_exists($atr->attribute->id, $attributes)) {
						$attributes[$atr->attribute->id] = $atr->attribute->short_name;
					}
					$rows[$entity->id][$atr->attribute->id] = $atr->data;
				}
				$ticket_data['ref_id'] = $entity->id;
				$ticket_data['ref_table'] = 'ot_position';
				$rows[$entity->id]['ticket'] = '<button onclick=fill_dialog(' . json_encode($ticket_data) . ')>Ticket</button>';

			}
			$content[] = '<table class="position-table"><thead><tr>';
			$attributes['ticket'] = 'Ticket';
			foreach ($attributes as $atr_id => $atr_name) {
				$content[] = '<th>' . $atr_name . '</th>';
			}
			$content[] = '</tr></thead><tbody>';
			foreach ($rows as $entity_id => $row) {
				$content[] = '<tr>';
				foreach ($attributes as $atr_id => $atr_name) {
					if (array_key_exists($atr_id, $row)) {
						$content[] = "<td>$row[$atr_id]</td>";
					} else {
						$content[] = "<td>&nbsp;</td>";
					}
				}
				$content[] = '</tr>';
			}
			$content[] = '</tbody></table>';
			
		} else {
			$content[] = '<p>Keine Positionen vorhanden</p>';
		}
		$data['positions'] = array('name' => $name, 'content' => join('', $content), 'id' => $id);
		
		// Tickets 
		//$tickets = $order->tickets->all();
		$tickets = array();
		$name = 'Tickets (' . count($tickets) . ')';
		$id = 'tickets_' . $order->id;
		$content = array();
		if ($objects = $tickets) {
			$content[] = '<table>';
			foreach ($objects as $id => $entity) {
				$content[] = '<tr>';
					$content[] = '<td>';
						$content[] = $entity->id;
					$content[] = '</td>';
				$content[] = '</tr>';
			}
			$content[] = '</table>';
		} else {
			$content[] = '<p>Keine Tickets vorhanden</p>';
		}
		$data['tickets'] = array('name' => $name, 'content' => join('', $content), 'id' => $id);
		
		$json['data'] = $data;
		
		$json['error'] = FALSE;
		break;
}

$errorMsg = ob_get_clean();

if ($errorMsg) {
	$json['error'] = TRUE;
	$json['errorMsg'] = $errorMsg;
}

echo json_encode($json);