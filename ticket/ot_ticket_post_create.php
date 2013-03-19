<?php
$fields = array('inquirer_title' => '', 'inquirer_first_name' => '', 'inquirer_last_name' => '',
				'inquirer_mail' => '', 'ticket_category_id' => 0, 'status' => 0, 'ref_table' => '', 'ref_id' => 0);

$timestamp = time();

$values = array();
foreach ($fields as $name => $default) {
	$values[$name] = (isset($_POST[$name])) ? "'$_POST[$name]'" : "'$default'";
}

$values['timestamp_created'] = $timestamp;

$query = "	INSERT INTO ot_ticket
				(" . join(', ', array_keys($values)) . ")
			VALUES (" . join(', ', $values) . ")";

$redirect = $ot->get_link('ticket');

MySQL::start_transaction();

if ($result = MySQL::query($query)) {
	$ticket = array('id' => MySQL::insert_id());
	
	$text = (isset($_POST['text'])) ? "'$_POST[text]'" : "''";
	
	$query = "	INSERT INTO ot_ticket_entry
					(ticket_id, text, timestamp_created)
				VALUES ($ticket[id], $text, $timestamp)";
	
	if ($result = MySQL::query($query)) {
		MySQL::commit();
		
		$redirect = $ot->get_link('ticket', $ticket['id']);
	}
}

header("Location: $redirect");



