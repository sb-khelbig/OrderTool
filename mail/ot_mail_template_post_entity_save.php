<?php $mail_template = MailTemplate::get($_GET['id']);

$text = isset($_POST['text']) ? $_POST['text'] : FALSE;

if ($text) {
	$mail_template->text = MySQL::escape($text);
	$mail_template->update();
}

$redirect = $ot->get_link('mail', $mail_template->id, 'template');
header("Location: $redirect");
