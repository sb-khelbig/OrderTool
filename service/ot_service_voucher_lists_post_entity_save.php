<?php $voucher_list = VoucherList::get($_GET['id']);

$data_source = isset($_POST['data_source']) ? DataSource::get($_POST['data_source']) : $voucher_list->data_source;

if ($voucher_list->data_source !== $data_source) {
	$voucher_list->data_source = $data_source;
	$voucher_list->update();
}

$redirect = $ot->get_link('service', $voucher_list->id, 'voucher_lists');
header("Location: $redirect");