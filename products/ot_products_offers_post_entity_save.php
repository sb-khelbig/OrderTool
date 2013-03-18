<?php 

$offer = Offer::get($_GET['id']);

$product = isset($_POST['product']) ? Product::get($_POST['product']) : $offer->product;
$supplier = isset($_POST['supplier']) ? Supplier::get($_POST['supplier']) : $offer->supplier;
$variants = isset($_POST['variants']) ? $_POST['variants'] : array();

$new_data_source = isset($_POST['new_data_source']) ? $_POST['new_data_source'] : array();
$new_ohds = isset($_POST['new_ohds']) ? $_POST['new_ohds'] : array();

if (($offer->product !== $product) || ($offer->supplier !== $supplier)) {
	$offer->product = $product;
	$offer->supplier = $supplier;
	$offer->update();
}

if (count($new_data_source) == count($new_ohds)) {
	$ohds = array();
	foreach ($new_data_source as $index => $id) {
		$new = new OfferHasDataSource();
		$new->Offer = $offer;
		$new->data_source = $id;
		$new->external_id = $new_ohds[$index];
		$ohds[] = $new;
	}
	if ($ohds) {
		OfferHasDataSource::bulk_save($ohds);
	}
}

$ovs = array();
if ($vars = $offer->variants->all()) {
	foreach ($vars as $var) {
		$ovs[$var->variant->id] = $var;
	}
}

if ($variants) {
	$new_ovs = array();
	foreach ($variants as $var_id => $ohv_id) {
		if (array_key_exists($var_id, $ovs)) {
			$ovs[$var_id] = null;
		} else {
			if (!$ohv_id) {
				$ov = new OfferHasVariant();
				$ov->offer = $offer;
				$ov->variant = ProductVariant::get($var_id);
				$new_ovs[] = $ov;
			}
		}
	}
	OfferHasVariant::bulk_save($new_ovs);
}

$delete = array();
foreach ($ovs as $ov) {
	if (is_object($ov)) {
		$delete[] = $ov->id;
	}
}

if ($delete) {
	$query = "	DELETE
				FROM ot_product_offer_has_variant
				WHERE id IN (" . join(', ', $delete) . ")";
	
	if ($result = MySQL::query($query)) {
		
	}
}

$redirect = $ot->get_link('products', $offer->id, 'offers');
header("Location: $redirect");