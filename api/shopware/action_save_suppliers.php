<?php $data_source = DataSource::get($_GET['id']);

$data_source->suppliers->all();

$suppliers = isset($_POST['supplier']) ? $_POST['supplier'] : array();

Supplier::all();

foreach ($suppliers as $id => $supplier) {
	if ($supplier) {
		$sds = SupplierHasDataSource::get($id);
		$sup = Supplier::get($supplier);
		if (!($sds->supplier === $sup)) {
			$sds->supplier = $sup;
			$sds->update();
		}
	}
}

$redirect = $ot->get_link('data_source', $data_source->id);
//header("Location: $redirect");