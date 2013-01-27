<?php
$import = get_row_by_id($_POST['id'], 'ot_import') or die("ID existiert nicht!");
$has_header = (isset($_POST['has_header'])) ? $_POST['has_header'] : 0;
$selected_headers = (isset($_POST['headers'])) ? $_POST['headers'] : die("Keine Header übergeben!");

mysql_query("START TRANSACTION");

// Header speichern
if ($has_header) {
	$result = mysql_query("	SELECT * FROM
							ot_import_column
							WHERE import_row_id = $has_header") or die('MYSQLError: ' . mysql_error());
	$headers = array();
	while ($row = mysql_fetch_assoc($result)) {
		$header_id = $selected_headers[$row['pos']];
		$headers[] = "($import[id], $header_id, $row[pos], '$row[data]')";
	}
	
	// Headerzeile markieren
	$result = mysql_query(" UPDATE ot_import_row
							SET error = 1
							WHERE id = $has_header") or die('MYSQLError: ' . mysql_error());
} else {
	$headers = array();
	foreach ($selected_headers as $pos => $header_id) {
		$headers[] = "($import[id], $header_id, $pos, '')";
	}
}
$values = join(', ', $headers);
$result = mysql_query("	INSERT INTO ot_import_has_header (import_id, header_id, pos, original_label)
						VALUES $values") or die('MYSQLError: ' . mysql_error());

// Order List anlegen
$order_list = array('name' => (isset($_POST['list_name'])) ? (($_POST['list_name']) ? $_POST['list_name'] : $import['file_name']) : $import['file_name']);
$result = mysql_query("	INSERT INTO ot_order_list (name)
						VALUES ('$order_list[name]')") or die('MYSQLError: ' . mysql_error());
$order_list['id'] = mysql_insert_id();

// Header anlegen
$result = mysql_query("	INSERT INTO ot_order_list_has_header (order_list_id, header_id, pos, original_label)
							SELECT $order_list[id], h.header_id, h.pos, h.original_label
							FROM ot_import_has_header AS h
							WHERE import_id = $import[id]") or die('MYSQLError: ' . mysql_error());

// Rows anlegen
$result = mysql_query("	INSERT INTO ot_row (order_list_id, import_row_id)
							SELECT $order_list[id], id
							FROM ot_import_row
							WHERE import_id = $import[id]
								AND error = 0") or die('MYSQLError: ' . mysql_error());

// Columns anlegen
$result = mysql_query("	INSERT INTO ot_column (row_id, pos, data)
							SELECT r.id, c.pos, c.data
							FROM ot_import_column AS c, ot_row AS r
							WHERE c.import_row_id = r.import_row_id
								AND r.error = 0") or die('MYSQLError: ' . mysql_error());

// TODO: Status anlegen

// TODO: Loggen

// Import updaten
$timestamp = time();
$has_header = ($has_header) ? 1 : 0;
$result = mysql_query("	UPDATE ot_import
						SET	timestamp_stored = $timestamp,
							opt_has_header = $has_header,
							opt_order_list = $order_list[id]
						WHERE id = $import[id]") or die('MYSQLError: ' . mysql_error());

mysql_query("COMMIT");

header("Location: index.php?p=orderlists&id=$order_list[id]");