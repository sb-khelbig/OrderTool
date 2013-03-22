<?php include __DIR__ . '/../mail/ot_mail_classes.php';

function confirmation_mail($ticket, $participant) {
	if ($mail = $participant->mail) {
		$id = $ticket->id;
		$name = $participant->last_name;
		$title = $participant->title();
		$salutation = ($name) ? "$title $name" : 'Kunde';
		$html = "Sehr geehrter $salutation,

vielen Dank für Ihre Nachricht.

Über folgenden Link haben Sie Zugriff auf Ihr Ticket: <a href=\"#\">#$id</a>.

Wir werden Sie über den Status Ihres Tickets per Mail informieren.


Mit freundlichen Grüßen
Ihr soforteinloesen.de Team";
		
		$mailer = new OrderToolMailer('smtp.gmail.com', 'ecommerce@salesbutlers.com', 'Salesbutlers2012+');
		
		$response = $mailer->send_mail(
				array(
						'address' => 'no-reply@salesbutlers.com',
						'name' => 'soforteinloesen.de'
						),
				'ecommerce@salesbutlers.com',
				"Ihr Ticket #$id",
				array(
						'html' => nl2br($html),
						'text' => $html
						)
			);
		
		return $response;
	}
	return array('success' => FALSE, 'error' => 'Keine Mail-Adresse gefunden!');
} ?>