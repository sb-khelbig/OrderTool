<?php
$fields = array('inquirer_title' => '', 'inquirer_first_name' => '', 'inquirer_last_name' => '',
				'inquirer_mail' => '', 'ticket_category_id' => 0, 'status' => 0, 'ref_table' => '', 'ref_id' => 0);

$timestamp = time();

$values = array();
foreach ($fields as $name => $default) {
	$values[$name] = (isset($_POST[$name])) ? "'$_POST[$name]'" : "'$default'";
}

$values['timestamp_created'] = $timestamp;

$cols = join(', ', array_keys($values));
$values = join(', ', $values);
$result = mysql_query("	INSERT INTO ot_ticket
							($cols)
						VALUES ($values)") or die('MySQLError: ' . mysql_error());

$ticket= array('id' => mysql_insert_id());

$text = (isset($_POST['text'])) ? "'$_POST[text]'" : "''";

$result = mysql_query("	INSERT INTO ot_ticket_entry
							(ticket_id, text, timestamp_created)
						VALUES ($ticket[id], $text, $timestamp)") or die('MySQLError: ' . mysql_error());

header("Location: index.php?p=ticket&id=$ticket[id]");
