<?php

$kunde = "
Sehr geehrte(r) {{ salutation }},

in Ihrem Ticket #{{ ticket.id }} ist eine neue Nachricht für Sie vorhanden.

Bitte loggen Sie sich in Ihrem Kundenkonto auf soforteinloesen.de ein, um Ihre Nachricht zu lesen und zu antworten.

Sollten Sie nicht innerhalb von 48 Stunden auf die Nachricht antworten, sehen wir das Problem als gelöst an.

Mit freundlichen Grüßen
Ihr soforteinloesen.de Team
";

$partner = "
Hallo {{ salutation }},

im Ticket #{{ ticket.id }} liegt eine neue Nachricht für dich vor.

Über folgenden Link kannst du zum Ticket gelangen: <a href=\"{{ ticket.url }}\" target=\"_blank\">{{ ticket.url }}</a>.

Viele Grüße
soforteinloesen.de Team
";

function fillVariables($ticket, $participant) {
	switch ($participant->type) {
		case 1:
			$title = ($participant->title) ? $participant->title() : FALSE;
			$name = $participant->last_name;
			$salutation = ($title && $name) ? "$title $name" : 'Kunde';
			$text = $GLOBALS['kunde'];
			break;
		case 2:
			$salutation = $participant->first_name;
			$text = $GLOBALS['partner'];
			break;
		default:
			return FALSE;
	}
	
	$variables = array(
			'salutation' => $salutation,
			'ticket.id' => $ticket->id,
			'ticket.url' => "http://ordertool.de/ticket?token=" . $participant->token,
		);
	
	foreach ($variables as $tag => $value) {
		$text = str_replace("{{ $tag }}", $value, $text);
	}
	
	return trim($text);
}

