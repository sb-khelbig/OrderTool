<?php
if (isset($_POST['headers'])) {
	$cases = array();
	foreach ($_POST['headers'] as $id => $header_id) {
		$cases[$id] = "id = $id THEN $header_id";
	}
	$values = join(', ', array_keys($cases));
	$cases = join(' WHEN ', $cases);
	$result = mysql_query("	UPDATE ot_order_list_has_header
							SET header_id = CASE
								WHEN $cases END
							WHERE id IN ($values)");
	if ($result) {
		$json = true;
	} else {
		$json = false;
	}
} else {
	$json = false;
} ?>

<div id="ajax_result"><?php echo json_encode($json);?></div>
