<?php include('../db/connection.php'); include('../db/functions.php');

switch ($_GET['action']) {
	
	case 'list':
		if ($order_list = get_row_by_id($_GET['id'], 'ot_order_list')) {
			$headers = mysql_query("SELECT hl.id AS id, h.name AS name
									FROM ot_header as h
										JOIN ot_order_list_has_header as hl
										ON h.id=hl.header_id
									WHERE hl.order_list_id=$order_list[id]");
			$json = array();
			while ($header = mysql_fetch_assoc($headers)) {
				$json[$header['id']] = $header['name'];
			}
		} else {
			$json = array();
		}
		break;
		
	case 'headers':
		if ($order_list = get_row_by_id($_GET['id'], 'ot_order_list')) {
			$headers = mysql_query("SELECT h.id AS id, hl.pos AS pos
									FROM ot_header as h
										JOIN ot_order_list_has_header as hl
										ON h.id=hl.header_id
									WHERE hl.order_list_id=$order_list[id]");
			$json = array();
			while ($header = mysql_fetch_assoc($headers)) {
				$json[$header['pos']] = $header['id'];
			}
		} else {
			$json = array();
		}
		break;
		
	default:
		$json = array();
}

echo json_encode($json);