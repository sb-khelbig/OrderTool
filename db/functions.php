<?php
class MYSQLException extends Exception {
}

/**
 * Einen Datenbankeintrag mittels der ID abfragen.
 * @param multitype:string|int $id ID des Eintrags
 * @param string $table Tabelle
 * @param string $identifier Name des ID-Feldes
 * @return multitype:array|int Datenbankeintrag oder 0 bei einem Fehler
 */
function get_row_by_id($id, $table, $identifier='id') {
	if ($result = mysql_query("SELECT * FROM $table WHERE $identifier=" . mysql_real_escape_string($id))) {
		while ($row = mysql_fetch_assoc($result)) {
			return $row;
		}
	}
	return 0;
}

function get_row_by_id_as_array($id, $table, $identifier='id') {
	if ($result = mysql_query("SELECT * FROM $table WHERE $identifier=" . mysql_real_escape_string($id))) {
		while ($row = mysql_fetch_assoc($result)) {
			return $row;
		}
	}
	return array ('id' => 0);
}

/**
 * Durchgeführte Funktion loggen
 * @param string|int $action_id ID der Aktion
 * @param string|int $ref_id ID des Eintrags
 * @return string Fehlermeldung oder leerer String
 */
function log_action($action_id, $ref_id) {
	$timestamp = time();
	$result = mysql_query("INSERT INTO ot_action_log (user_id, ref_id, action_id, timestamp_created) VALUES ($_SESSION[UserID], $ref_id, $action_id, $timestamp)");
	return mysql_error();
}

/**
 * Führt ein Query aus und wirft einen Fehler falls das Ergebnis False ist
 * @param string $query
 * @throws MYSQLException
 * @return resource
 */
function mysql_query_with_error($query) {
	if ($result = mysql_query($query)) {
		return $result;
	} else {
		throw new MYSQLException(mysql_error());
	}
}
?>