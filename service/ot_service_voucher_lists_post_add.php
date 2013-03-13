<?php

$name = isset($_POST['name']) ? $_POST['name'] : false;

if ($name) {
	$voucher_list = new VoucherList();
	$voucher_list->name = $name;
	$voucher_list->save();
	
	$redirect = $ot->get_link('service', $voucher_list->id, 'voucher_lists');
} else {
	$redirect = $ot->get_link('service', 0, 'voucher_lists');
}

header("Location: $redirect");

