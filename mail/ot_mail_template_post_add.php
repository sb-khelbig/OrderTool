<?php
$name = isset($_POST['name']) ? $_POST['name'] : '';

if ($name) {
	$mail_template = new MailTemplate();
	$mail_template->name = $name;
	$mail_template->save();
	
	$redirect = $ot->get_link('mail', $mail_template->id, 'template');
} else {
	$redirect = $ot->get_link('mail', 0, 'template');
}


header("Location: $redirect");