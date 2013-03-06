<?php include 'mail/ot_mail_classes.php';

$ticket = get_row_by_id($_GET['id'], 'ot_ticket') or die('ID existiert nicht!');

$template = get_row_by_id((isset($_POST['template'])) ? $_POST['template'] : 0, 'ot_mail_template');

$partner = (isset($_POST['partner'])) ? $_POST['partner'] : 0;

$account = get_row_by_id((isset($_POST['mail_account_id'])) ? $_POST['mail_account_id'] : 1, 'ot_mail_account') or die('ID existiert nicht!');
$mailer = new OrderToolMailer($account['smtp'], $account['address'], $account['password'], $account['smtp_encryption']);
$from = array('name' => $account['name'], 'address' => $account['address']);

$content_customer = array('html' => (isset($_POST['text_customer'])) ? $_POST['text_customer'] : '');

$time = date('d.m.Y G:i', $ticket['timestamp_created']);
$mailer->send_mail($from, 'oliver.zander@salesbutlers.com', "Re: Ihre Anfrage vom $time [#$ticket[id]]", $content_customer);

//header("Location: index.php?p=ticket&id=$ticket[id]");

