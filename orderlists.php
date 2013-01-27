<?php
if ($_SERVER["REQUEST_METHOD"]=='POST') {
	if (isset($_GET['id'])) {
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'changename':
					include 'orderlists/ot_orderlists_post_changename.php';
					break;
				case 'changeheader':
					include 'orderlists/ot_orderlists_post_changeheader.php';
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
		include 'orderlists/ot_orderlists_get_entity.php';
	} else {
		include 'orderlists/ot_orderlists_get_overview.php';
	}
} else {
	echo "Method not supported.";
}