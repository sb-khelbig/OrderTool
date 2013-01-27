<?php 
$import = get_row_by_id($_GET['id'], 'ot_import') or die("ID existiert nicht!");

$has_header = (isset($_POST['has_header'])) ? 1 : 0;

// ersten 5 Datenspalten abrufen
$limit = 5 + $has_header;
$result = mysql_query("	SELECT id
						FROM ot_import_row
						WHERE import_id = $import[id]
						LIMIT $limit");
$row_ids = array();
while ($row = mysql_fetch_assoc($result)) {
	$row_ids[] = $row['id'];
}
$values = join(', ', $row_ids);
$result = mysql_query("	SELECT *
						FROM ot_import_column
						WHERE import_row_id IN ($values)");

// Ausgabearray zusammensetzen
$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	if (array_key_exists($row['import_row_id'], $rows)) {
		$rows[$row['import_row_id']][$row['pos']] = $row['data'];
	} else {
		$rows[$row['import_row_id']] = array($row['pos'] => $row['data']);
	}
}

// Headerzeile speichern
$has_header = ($has_header) ? $row_ids[0] : 0;