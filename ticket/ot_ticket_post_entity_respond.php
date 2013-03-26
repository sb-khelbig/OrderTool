<?php $ticket = Ticket::get($_GET['id']);

$participants = isset($_POST['participant']) ? $_POST['participant'] : array();

$msg = isset($_POST['message']) ? trim($_POST['message']) : FALSE;

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
	
	$rights[] = new TicketEntryRight(array('entry_id' => $entry, 'participant_id' => $sender));
	
	if ($participants) {
		include __DIR__ . '/../mail/ot_mail_classes.php';
		include __DIR__ . '/ot_ticket_templates.php';
		
		foreach ($ticket->participants->all() as $participant) {
			if (array_key_exists($participant->id, $participants)) {
				if ($sender->id != $participant->id) {
					if ($mail = fillVariables($ticket, $participant)) {
					
						$mailer = new OrderToolMailer('smtp.gmail.com', 'ecommerce@salesbutlers.com', 'Salesbutlers2012+');
						
						$response = $mailer->send_mail(
								array(
									'address' => 'no-reply@salesbutlers.com',
									'name' => 'soforteinloesen.de'
									),
								$participant->mail,
								"Neue Nachricht in Ticket #" . $ticket->id,
								array(
									'html' => nl2br($mail),
									'text' => $mail
									)
							);
					}
					$rights[] = new TicketEntryRight(array('entry_id' => $entry, 'participant_id' => $participant));
				}
			}
		}
	}
	TicketEntryRight::bulk_save($rights);
}

$redirect = $ot->get_link('ticket', $ticket->id);
header("Location: $redirect");