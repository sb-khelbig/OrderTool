<?php include ('../db/connection.php');

if ($_SERVER["REQUEST_METHOD"]=='POST') {
	$json = array('succes' => false, 'error' => 'Method not supported!');
	
} elseif ($_SERVER["REQUEST_METHOD"]=='GET') {
	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		if (isset($_GET['action'])) {
			switch ($_GET['action']) {
				case 'gettemplate':
					$result = mysql_query("	SELECT text
											FROM ot_mail_template
											WHERE id = $id");
					if ($result) {
						if ($data = mysql_fetch_assoc($result)) {
							$json = array('success' => true, 'data' => $data);
						} else {
							$json = array('success' => false, 'error' => 'No result!');
						}
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
