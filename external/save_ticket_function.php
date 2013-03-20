<?php
function save_ticket($category, $table, $references, $text, $data) {
	$ticket = new Ticket();
	$ticket->category = $category;
	$ticket->ref_table = $table::getTableName();
	$ticket->created = time();
	
	// Title
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['title'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$ticket->inquirer_title = ($value) ? (($value->data == 'ms') ? FALSE : TRUE) : FALSE;
	
	// FirstName
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['first_name'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$ticket->inquirer_first_name = ($value) ? $value->data : '';
	
	// LastName
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['last_name'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$ticket->inquirer_last_name = ($value) ? $value->data : '';
	
	// Mail
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['mail'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$ticket->inquirer_mail = ($value) ? $value->data : '';
	
	$entry = new TicketEntry();
	$entry->created = time();
	$entry->text = MySQL::escape($text);
	$ticket->entries->add($entry);
	
	foreach ($references as $reference) {
		$ticket_ref = new TicketReference();
		$ticket_ref->ref_id = $reference;
		$ticket->references->add($ticket_ref);
	}
	
	Ticket::bulk_save(array($ticket));
	
	return $ticket->id;
} ?>