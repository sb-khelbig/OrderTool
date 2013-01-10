<?php
/**
 * Funktion um ein HTML-Dropdownmenu auf Basis von Werten einer Datenbanktabelle zu erzeugen
 * @param string $name Name-Attribut des Select-Tags
 * @param string $table Datenbanktabelle
 * @param string $initial vordefinierte Option (optional)
 * @param string $text Name des Datenbankfeldes für den Text
 * @param string $value Name des Datenbankfeldes für den Value-Tag
 * @return string erstelltes Menu
 */
function create_dropdown_menu($name, $table, $initial=False, $text='name', $value='id') {
	$select = '<select name="' . $name . '">';
	$result = mysql_query("SELECT $value, $text FROM $table");
	$options = array();
	if ($initial) {
		$options[] = '<option value="0">' . $initial . '</option>';
	}
	while ($option = mysql_fetch_assoc($result)) {
		$options[] = '<option value="' . $option[$value] . '">' . $option[$text] . '</option>';
	}
	return $select . join('', $options) . '</select>';
} 
?>