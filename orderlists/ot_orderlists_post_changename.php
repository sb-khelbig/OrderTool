<?php
$id = mysql_real_escape_string($_GET['id']);
if (isset($_POST['name'])) {
	$name = mysql_real_escape_string($_POST['name']);
	$result = mysql_query("	UPDATE ot_order_list
							SET name = '$name'
							WHERE id = $id");
	if ($result) {
		$json = true;
	} else {
		$json = false;
	}
} else {
	$json = false;
}
 ?>

<div id="ajax_result"><?php echo json_encode($json); ?></div>
