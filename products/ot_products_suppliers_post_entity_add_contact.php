<?php $supplier = Supplier::get($_GET['id']);

$contact = (isset($_POST['contact'])) ? trim($_POST['contact']) : die('No contact submitted!');

if (!$contact) {
	$title = isset($_POST['title']) ? trim($_POST['title']) : 0;
	$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
	$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
	$mail = isset($_POST['mail']) ? trim($_POST['mail']) : '';
	
	if (!(!$first_name && !$last_name && !$mail)) {
		$contact = new Contact(array(
				'title' => $title,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'mail' => $mail,
			)
		);
		$contact->save();
	} else {
		die('No contact submitted!');
	}
}

$shc = new SupplierHasContact();
$shc->supplier = $supplier;
$shc->contact = $contact;
$shc->save();

$redirect = $ot->get_link('products', $supplier->id, 'suppliers');
header("Location: $redirect");