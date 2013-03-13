<?php $voucher_list = VoucherList::get($_GET['id']);

if (isset($_FILES['csv'])) {
	$file = $_FILES['csv'];
	if (!$file['error']) {
		if ($f = fopen($file['tmp_name'], 'r')) {
			$headers = array_flip(fgetcsv($f, 0, ';'));
			if ($c_row = isset($headers['code']) ? $headers['code'] : FALSE) {
				$codes = array();
				while ($row = fgetcsv($f, 0, ';')) {
					if ($code = isset($row[$c_row]) ? $row[$c_row] : false) {
						$vc = new VoucherCode();
						$vc->code = $code;
						$vc->voucher_list = $voucher_list;
						$codes[] = $vc;
					}
				}
				VoucherCode::bulk_save($codes);
			}
			fclose($f);
		}
	} else {
		die($file['error']);
	}
}

$redirect = $ot->get_link('service', $voucher_list->id, 'voucher_lists');
header("Location: $redirect");