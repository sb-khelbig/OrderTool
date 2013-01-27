<?php
$import = get_row_by_id($_POST['id'], 'ot_import') or die("ID existiert nicht!");
$order_list = get_row_by_id($_POST['list'], 'ot_order_list') or die("ID existiert nicht!");
$selected_headers = (isset($_POST['headers'])) ? $_POST['headers'] : die("Header not set!");

// Header
$result = mysql_query("	SELECT id, pos, header_id
						FROM ot_order_list_has_header
						WHERE order_list_id = $order_list[id]");
$headers = array();
while ($row = mysql_fetch_assoc($result)) {
	$headers[$row['id']] = $row;
}

// Matching
$matching = (array_key_exists($_POST['matching'], $headers)) ? $headers[$_POST['matching']] : die("ID existiert nicht!");

