<?php
if ($_SERVER["REQUEST_METHOD"]=='POST') {
	if (isset($_GET['id'])) {
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'newlist':
					include 'import/ot_import_action_newlist.php';
					break;
				case 'existinglist':
					include 'import/ot_import_action_existinglist.php';
					break;
				case 'statusupdate':
					include 'import/ot_import_action_statusupdate.php';
					break;
				default:
					echo 'Action not supported!';
			}
		} else {
			echo 'Action not set!';
		}
	} else {
		if (isset($_POST['action'])) {
			include ('db/ot_db_logging.php');
			switch ($_POST['action']) {
				case 'upload':
					include 'import/ot_import_post_upload.php';
					break;
				case 'store':
					include 'import/ot_import_post_store.php';
					break;
				case 'update':
					if (isset($_POST['matching'])) {
						if ($_POST['matching']) {
							include 'import/ot_import_post_match.php';
						} else {
							include 'import/ot_import_post_append.php';
						}
					} else {
						echo 'Matching not set!';
					}
					break;
				default:
					echo 'Action not supported!';
			}
		} else {
			echo 'Action not set!';
		}
	}
} elseif ($_SERVER["REQUEST_METHOD"]=='GET') {
	if (isset($_GET['id'])) {
		include 'import/ot_import_get_entity.php';
	} else {
		include 'import/ot_import_get_overview.php';
	}	
} else {
	echo 'Method not supported!';
}