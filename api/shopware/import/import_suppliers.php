<?php $data_source = DataSource::get($_GET['id']);

$options = $data_source->getOptionsArray();

$existing = array();
foreach ($data_source->suppliers->all() as $sup) {
	$existing[$sup->external_id] = $sup;
}

include __DIR__ . '/../db_connection.php';

$query = "	SELECT id, name
			FROM s_articles_supplier";

$suppliers_extern = array();
if ($result = MySQL_extern::query($query)) {
	while ($row = MySQL_extern::fetch($result)) {
		if (!array_key_exists($row['id'], $existing)) {
			$supex = new SupplierHasDataSource();
			$supex->data_source = $data_source;
			$supex->external_name = $row['name'];
			$supex->external_id = $row['id'];
			$suppliers_extern[] = $supex;
		}
	}
}

SupplierHasDataSource::bulk_save($suppliers_extern);

$redirect = $ot->get_link('data_source', $data_source->id);
header("Location: $redirect");
