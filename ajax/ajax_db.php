<?php include '../db/connection.php';
$json = array();
$options = 0;

if ($_SERVER["REQUEST_METHOD"]=='GET') {
	if (isset($_GET['action'])) {
		switch ($_GET['action']) {
			
			case 'auth':
				break;
				
			case 'select':
				$fields = (isset($_GET['fields'])) ? $_GET['fields'] : '*';
				$table = (isset($_GET['table'])) ? $_GET['table'] : false;
				$terms = (isset($_GET['terms'])) ? $_GET['terms'] : '1';
				if ($table) {
					$result = mysql_query("SELECT $fields FROM $table WHERE $terms");
					if ($result) {
						while ($row = mysql_fetch_assoc($result)) {
							$json[] = $row;
						}
					} else {
						$json['error'] = 'MYSQLError: ' . mysql_error();
					}
				} else {
					$json['error'] = "Table is missing!";
				}
				break;
			
			case 'complex':
				break;
				
			default:
				$json['error'] = "Action not supported!";
		}
	} else {
		$json['error'] = "Action not set!";
	}
} elseif ($_SERVER["REQUEST_METHOD"]=='POST') {
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
				
			default:
				$json['error'] = "Action not supported!";
		}
	}
} else {
	$json['error'] = "Method not supported!";
}

echo json_encode($json, $options);