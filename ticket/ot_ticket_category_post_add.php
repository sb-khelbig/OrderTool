<?php
$name = isset($_POST['name']) ? $_POST['name'] : '';

if ($name) {
	$ticket_category = new TicketCategory();
	$ticket_category->name = $name;
	$ticket_category->save();
}

$redirect = $ot->get_link('ticket', $ticket_category->id, 'category');
header("Location: $redirect");