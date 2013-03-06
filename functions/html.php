<?php
/**
 * Funktion um ein HTML-Dropdownmenu auf Basis von Werten einer Datenbanktabelle zu erzeugen
 * @param string $name Name-Attribut des Select-Tags
 * @param string $table Datenbanktabelle
 * @param string $initial vordefinierte Option (optional)
 * @param string $selected vorausgewählte ID
 * @param string $text Name des Datenbankfeldes für den Text
 * @param string $value Name des Datenbankfeldes für den Value-Tag
 * @return string erstelltes Menu
 */
function create_dropdown_menu($name, $table, $initial=False, $selected='0', $text='name', $value='id') {
	$select = '<select name="' . $name . '">';
	$result = mysql_query("SELECT $value, $text FROM $table");
	$options = array();
	if ($initial) {
		$options[] = '<option value="0">' . $initial . '</option>';
	}
	while ($option = mysql_fetch_assoc($result)) {
		if ($option[$value] == $selected) {
			$options[] = '<option value="' . $option[$value] . '" selected="selected" >' . $option[$text] . '</option>';
		} else {
			$options[] = '<option value="' . $option[$value] . '">' . $option[$text] . '</option>';
		}
	}
	return $select . join('', $options) . '</select>';
}

?>