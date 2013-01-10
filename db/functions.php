<?php
function get_row_by_id($id, $table, $identifier='id') {
	$result = mysql_query("SELECT * FROM $table WHERE $identifier=" . mysql_real_escape_string($id));
	while ($row = mysql_fetch_assoc($result)) {
		return $row;
	}
	return False;
}
?>