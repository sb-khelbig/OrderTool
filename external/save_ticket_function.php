<?php
function generate_token($object, $key, $algo = 'sha256') {
	$data = $object->toArray();
	$data['key'] = $key;
	$token = json_encode($data);
	return hash($algo, $token);
}

function save_ticket($category, $table, $references, $text, $data) {
	$ticket = new Ticket();
	$ticket->category = $category;
	$ticket->ref_table = $table::getTableName();
	$ticket->created = time();
	
	$participant = new TicketParticipant();
	$ticket->participants->add($participant);
	$participant->type = 1;
	
	// Title
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['title'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$participant->title = ($value) ? (($value->data == 'ms') ? 2 : 1) : 0;
	
	// FirstName
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['first_name'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$participant->first_name = ($value) ? $value->data : '';
	
	// LastName
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['last_name'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$participant->last_name = ($value) ? $value->data : '';
	
	// Mail
	$value = Value::filter(array(
			'ref_id' => $data['reference'],
			'attribute_id' => $data['mail'],
		)
	);
	$value = ($value) ? array_pop($value) : FALSE;
	$participant->mail = ($value) ? $value->data : '';
	
	$entry = new TicketEntry();
	$ticket->entries->add($entry);
	$entry->participant = $participant;
	$entry->created = time();
	$entry->text = MySQL::escape($text);
	
	foreach ($references as $reference) {
		$ticket_ref = new TicketReference();
		$ticket->references->add($ticket_ref);
		$ticket_ref->ref_id = $reference;
	}
	
	$entry_right = new TicketEntryRight();
	$entry->rights->add($entry_right);
	$entry_right->participant = $participant;
	
	$tickets = array($ticket);
	
	Ticket::bulk_save($tickets);
	
	include __DIR__ . '/confirmation_mail.php';
	
	$participant->token = generate_token($participant, $GLOBALS['key']);
	$participant->update();
	
	$response = confirmation_mail($ticket, $participant);
	
	if (!$response['success']) {
		throw new Exception($response['error']);
	} 
	
	return $ticket->id;
} ?>