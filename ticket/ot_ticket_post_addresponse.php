<?php
$ticket = get_row_by_id($_GET['id'], 'ot_ticket') or die("ID existiert nicht!");

$text = (isset($_POST['text'])) ? trim($_POST['text']) : '';

if ($text) {
	$timestamp = time();
	$result = mysql_query("	INSERT INTO ot_ticket_entry
								(ticket_id, timestamp_created, text)
							VALUES ($ticket[id], $timestamp, '$text')") or die (mysql_error());
	
	header("Location: index.php?p=ticket&id=$ticket[id]");
	
} else {
	die('Kein Text eingegeben!');
}
