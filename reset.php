<?php
include ('db/connection.php');

$result = mysql_query('SHOW TABLES');
while($row = mysql_fetch_row($result)) {
	if (mysql_query("TRUNCATE $row[0]")) {
		echo "<p>Tabelle $row[0] erfolgreich geleert.<br />";
	}
	if (mysql_query("ALTER TABLE $row[0] AUTO_INCREMENT=1")) {
		echo "AUTO_INCREMENT von Tabelle $row[0] erfolgreich zurueckgesetzt.</p>";
	}
}
?>