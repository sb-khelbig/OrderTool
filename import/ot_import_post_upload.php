<?php
if ($_FILES['file']['error'] == 0) {
	if ($f = fopen($_FILES['file']['tmp_name'], 'r')) {
		// Transaktion starten
		mysql_query("START TRANSACTION");
		
		// Import anlegen
		$timestamp = time();
		$file_name = $_FILES['file']['name'];
		$result = mysql_query("	INSERT INTO ot_import (file_name, timestamp_created)
								VALUES ('$file_name', $timestamp)") or die('MYSQLError: ' . mysql_error());
		$import = array('id' => mysql_insert_id());
		
		// Datei auslesen
		$rows = array(); $columns = array(); $row_count = 0;
		while ($row = fgetcsv($f, 0, ';')) {
			$row_count++;
			$rows[] = "($import[id])";
			$columns[] = $row;
		}
		
		// Rows anlegen
		$values = join(', ', $rows);
		$result = mysql_query("	INSERT INTO ot_import_row (import_id)
								VALUES $values") or die('MYSQLError: ' . mysql_error());
		
		// Row IDs auslesen
		$result = mysql_query("	SELECT id
								FROM ot_import_row
								WHERE import_id = $import[id]") or die('MYSQLError: ' . mysql_error());
		
		// Columns anlegen
		$i = 0;$query = array();
		while ($row_id = mysql_fetch_assoc($result)) {
			foreach ($columns[$i++] as $pos => $column) {
				$tmp = trim($column);
				$query[] = "($row_id[id], $pos, '$tmp')";
			}
		}
		$values = join(', ', $query);
		$result = mysql_query("	INSERT INTO ot_import_column (import_row_id, pos, data)
								VALUES $values") or die('MYSQLError: ' . mysql_error());
		
		// Zeilenanzahl speichern
		$result = mysql_query("	UPDATE ot_import
								SET file_row_count = $row_count
								WHERE id = $import[id]") or die('MYSQLError: ' . mysql_error());
		
		// Loggen
		$logger->add(1, $import['id']);
		
		// Änderungen übernehmen
		mysql_query("COMMIT");
		
		// Zur Import-Ansicht weiterleiten
		header("Location: index.php?p=import&id=$import[id]");
		
	} else {
		echo 'Datei konnte nicht geöffnet werden!';
	}
	
} else {
	echo 'Dateifehler, Code: ' . $_FILES['file']['error'];
}