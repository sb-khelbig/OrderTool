<?php
$data_source = DataSource::get($_GET['id']);
$atrs = isset($_POST['atr']) ? $_POST['atr'] : die("Fehler!");

// Caching
$data_source->attributes->all();
$attributes = Attribute::filter(array('id' => $atrs));

MySQL::start_transaction();
foreach ($atrs as $id => $atr_id) {
	$dts_atr = DataSourceHasAttribute::get($id);
	$a = is_object($dts_atr->attribute) ? $dts_atr->attribute->id : $dts_atr->attribute;
	if ($a != $atr_id) {
		$dts_atr->attribute = $atr_id;
		$dts_atr->update();
	}
}
MySQL::commit();

$redirect = $ot->get_link('data_source', $data_source->id);
header("Location: $redirect");