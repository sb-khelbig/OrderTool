<?php
/**
 * Einen Datenbankeintrag mittels der ID abfragen.
 * @param multitype:string|int $id ID des Eintrags
 * @param string $table Tabelle
 * @param string $identifier Name des ID-Feldes
 * @return multitype:array|boolean Datenbankeintrag oder False bei einem Fehler
 */
function get_row_by_id($id, $table, $identifier='id') {
	$result = mysql_query("SELECT * FROM $table WHERE $identifier=" . mysql_real_escape_string($id));
	while ($row = mysql_fetch_assoc($result)) {
		return $row;
	}
	return False;
}
?>