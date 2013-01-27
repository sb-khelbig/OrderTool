<?php
$import = get_row_by_id($_POST['id'], 'ot_import') or die("ID existiert nicht!");
$order_list = get_row_by_id($_POST['list'], 'ot_order_list') or die("ID existiert nicht!");
$selected_headers = (isset($_POST['headers'])) ? $_POST['headers'] : die("Header not set!");
$has_header = (isset($_POST['has_header'])) ? $_POST['has_header'] : 0;

// Header
$result = mysql_query("	SELECT id, pos, header_id
						FROM ot_order_list_has_header
						WHERE order_list_id = $order_list[id]") or die('MYSQLError: ' . mysql_error());
$headers = array();
while ($row = mysql_fetch_assoc($result)) {
	$headers[$row['header_id']] = $row;
}

// Positionsliste erstellen
$new_headers = array();
$positions = array();
foreach ($selected_headers as $pos => $id) {
	if (array_key_exists($id, $headers)) {
		$positions[$pos] = $headers[$id];
	} else {
		$new_headers[$pos] = $id;
	}
}

mysql_query("START TRANSACTION");

// Headerlabels auslesen
if ($has_header) {
	$result = mysql_query("	SELECT pos, data
							FROM ot_import_column AS c
							WHERE c.import_row_id = $has_header") or die('MYSQLError: ' . mysql_error());
	$labels = array();
	while ($row = mysql_fetch_array($result)) {
		$labels[$row['pos']] = $row['data'];
	}
	
	// Headerzeile markieren
	$result = mysql_query(" UPDATE ot_import_row
							SET error = 1
							WHERE id = $has_header") or die('MYSQLError: ' . mysql_error());
} else {
	$labels = array();
	foreach ($selected_headers as $pos => $id) {
		$labels[$pos] = '';
	}
}

// neue Header anlegen
if ($new_headers) {
	$pos_count = count($headers);
	$query = array();
	$pos_to_pos = array();
	foreach ($new_headers as $pos => $id) {
		$current_pos = $pos_count++;
		$query[] = "($order_list[id], $id, $current_pos, '$labels[$pos]')";
		$pos_to_pos[$current_pos] = $pos;
	}
	// speichern
	$values = join(', ', $query);
	$result = mysql_query("	INSERT INTO ot_order_list_has_header
								(order_list_id, header_id, pos, original_label
							VALUES ($order_list[id], $id, $current_pos, '$labels[$pos]')") or die('MYSQLError: ' . mysql_error());
	
	// neue IDs abfragen
	$pos = count($headers) - 1;
	$result = mysql_query("	SELECT id, pos, header_id
							FROM ot_order_list_has_header
							WHERE pos > $pos
								AND order_list_id = $order_list[id]") or die('MYSQLError: ' . mysql_error());
	
	// neue IDs der Positionsliste hinzufügen
	while ($row = mysql_fetch_assoc($result)) {
		$positions[$pos_to_pos[$row['pos']]] = $row;
	}
}

// Rows anlegen
$result = mysql_query("	INSERT INTO ot_row (order_list_id, import_row_id)
							SELECT $order_list[id], id
							FROM ot_import_row
							WHERE import_id = $import[id]") or die('MYSQLError: ' . mysql_error());

// Columns anlegen
$result = mysql_query("	SELECT r.import_row_id, r.id 
						FROM ot_row AS r, ot_import_row AS ir
						WHERE r.import_row_id = ir.id
							AND ir.import_id = $import[id]") or die('MYSQLError: ' . mysql_error());
$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	$rows[$row['import_row_id']] = $row['id'];
}

$result = mysql_query("	SELECT r.id, c.pos, c.data
						FROM ot_import_column AS c, ot_import_row as r
						WHERE r.id = c.import_row_id
							AND r.error = 0
							AND r.import_id = $import[id]") or die('MYSQLError: ' . mysql_error());
$columns = array();
while ($row = mysql_fetch_assoc($result)) {
	$row_id = $rows[$row['id']];
	$pos = $positions[$row['pos']]['pos'];
	$data = $row['data'];
	$columns[] = "($row_id, $pos, '$data')";
}
$values = join(', ', $columns);
$result = mysql_query("	INSERT INTO ot_column
							(row_id, pos, data)
						VALUES $values") or die('MYSQLError: ' . mysql_error());

// TODO: fehlende Spalten auffüllen

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