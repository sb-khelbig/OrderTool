<?php include ('../db/mysql.php'); include '../db/tables.php';

if ($_SERVER["REQUEST_METHOD"]=='POST') {
	$json = array('succes' => false, 'error' => 'Method not supported!');
	
} elseif ($_SERVER["REQUEST_METHOD"]=='GET') {
	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		if (isset($_GET['action'])) {
			switch ($_GET['action']) {
				case 'gettemplate':
					if ($template = MailTemplate::get($id)) {
						$json = array('success' => true, 'data' => $template->toArray());
					} else {
						$json = array('success' => false, 'error' => 'MySQLError: ' . mysql_error());
					}
					break;
				default:
					$json = array('succes' => false, 'error' => 'Action not supported!');
			}
		} else {
			$json = array('succes' => false, 'error' => 'Action not set!');
		}
	} else {
		$json = array('succes' => false, 'error' => 'ID not set!');
	}
} else {
	$json = array('succes' => false, 'error' => 'Method not supported!');
}

echo json_encode($json);
