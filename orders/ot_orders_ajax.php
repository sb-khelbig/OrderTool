<?php include '../db/mysql.php'; include '../db/tables.php';

$json = array('error' => FALSE, 'errorMsg' => 'Unbekannter Fehler');

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
			$content[] = '<table>';
			foreach ($objects as $entity) {
				$content[] = '<tr>';
					$content[] = '<td>';
						$content[] = $entity->id;
					$content[] = '</td>';
					foreach ($entity->attributes->all() as $atr) {
						$content[] = '<td>';
							$content[] = $atr->data;
						$content[] = '</td>';
					}
				$content[] = '</tr>';
			}
			$content[] = '</table>';
		} else {
			$content[] = '<p>Keine Positionen vorhanden</p>';
		}
		$data['positions'] = array('name' => $name, 'content' => join('', $content), 'id' => $id);
		
		// Tickets
		$tickets = $order->tickets->all();
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

echo json_encode($json);