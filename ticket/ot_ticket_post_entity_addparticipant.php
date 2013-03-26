<?php $ticket = Ticket::get($_GET['id']);

$contact = isset($_POST['contact']) ? $_POST['contact'] : die('No contact supplied!');

if ($contact) {
	$contact = Contact::get($contact);
	$participant = new TicketParticipant();
	$participant->title = $contact->title;
	$participant->first_name = $contact->first_name;
	$participant->last_name = $contact->last_name;
	$participant->mail = $contact->mail;
	
} else {
	$title = isset($_POST['title']) ? $_POST['title'] : 0;
	$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
	$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
	$mail = isset($_POST['mail']) ? $_POST['mail'] : '';
	
	if ($title && $first_name && $last_name && $mail) {
		$participant = new TicketParticipant();
		$participant->title = $title;
		$participant->first_name = $first_name;
		$participant->last_name = $last_name;
		$participant->mail = $mail;
		
	} else {
		die('Missing contact data!');
	}
}

$participant->type = 2;
$participant->ticket = $ticket;
$participant->save();
$participant->generate_token("CsqH5#`Ve/v`?v9T^dQ0ypcw@avsZHhn");

$entries = isset($_POST['entry']) ? $_POST['entry'] : array();

$rights = array();
foreach ($ticket->entries->all() as $entry) {
	if (array_key_exists($entry->id, $entries)) {
		$right = new TicketEntryRight();
		$right->entry = $entry;
		$right->participant = $participant;
		$rights[] = $right;
	}
}

$msg = isset($_POST['send_message']) ? $_POST['send_message'] : FALSE;
$msg = ($msg) ? (isset($_POST['message']) ? $_POST['message'] : '') : FALSE;

if ($msg) {
	$user = $GLOBALS['user'];
	
	if ($senders = TicketParticipant::filter(array('ticket_id' => $ticket, 'type' => 0, 'mail' => $user->mail))) {
		$sender = $senders[0];
	} else {
		$sender = new TicketParticipant(array(
				'ticket_id' => $ticket->id,
				'title' => $user->title,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'mail' => $user->mail,
			)
		);
	
		$sender->save();
		$sender->generate_token("CsqH5#`Ve/v`?v9T^dQ0ypcw@avsZHhn");
	}
	
	$entry = new TicketEntry();
	$entry->ticket = $ticket;
	$entry->participant = $sender;
	$entry->created = time();
	$entry->text = MySQL::escape($msg);
	$entry->save();
	$right = new TicketEntryRight();
	$right->entry = $entry;
	$right->participant = $sender;
	$rights[] = $right;
	$right = new TicketEntryRight();
	$right->entry = $entry;
	$right->participant = $participant;
	$rights[] = $right;
}

TicketEntryRight::bulk_save($rights);

$redirect = $ot->get_link('ticket', $ticket->id);
header("Location: $redirect");
