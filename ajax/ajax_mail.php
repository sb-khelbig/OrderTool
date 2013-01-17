<?php include ('../db/connection.php'); include('../mail/mail_connection.php');?>

<?php
$id = $_GET['id'];

$section = 1;

$struc = imap_fetchstructure($imap, $id, FT_UID);
switch ($struc->type) {
	case 0:
		$section = 1;
		break;
		
	default:
		break;
}

$body = imap_fetchbody($imap, $id, $section, FT_UID);

echo mb_convert_encoding(quoted_printable_decode($body), 'UTF-8');
?>