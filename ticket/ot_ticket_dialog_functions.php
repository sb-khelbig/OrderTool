<?php 
function get_data($name, $default = '') {
	return isset($create_ticket_data[$name]) ? $create_ticket_data[$name] : $default;
} 

function get_select($name, $current) {
	$value[] = "value=\"$current\"";
	if (isset($create_ticket_data[$name])) {
		if ($current == $create_ticket_data[$name]) {
			$value[] = 'selected="selected"';
		}
	}
	return join(' ', $value);
} ?>