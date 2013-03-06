<?php include '../db/mysql.php'; include '../db/tables.php';

$json = array('error' => 'Unknown error occured!');

$request = $_SERVER['REQUEST_METHOD'];

if ($request == 'GET') {
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
		switch ($action) {
			case 'attribute':
				if (isset($_GET['id'])) {
					$id = mysql_real_escape_string($_GET['id']);
					try {
						$attribute = Attribute::get($id);
						$json['data'] = $attribute->toArray();
						foreach ($attribute->choices() as $value) {
							$json['data']['select'][] = $value->toArray();
						}
						$json['error'] = false;
					} catch (MySQLError $e) {
						$json['error'] = "$e";
					}
				} else {
					$json['error'] = "No ID set!";
				}
				break;
			default:
				$json['error'] = "Action '$action' not supported!";
		}
	} else {
		$json['error'] = "No action set!";
	}
} else {
	$json['error'] = "$request not supported!";
}

echo json_encode($json);