<?php $product = Product::get($_GET['id']);

$values = isset($_POST['value']) ? $_POST['value'] : array();

if ($values) {
	// Caching
	Value::filter(array('id' => array_keys($values)));
	
	$existing = array();
	foreach ($product->attributes->all() as $pav) {
		$existing[$pav->value->id] = $pav;
	}

	$pavs = array();
	foreach ($values as $id => $value) {
		if (array_key_exists($id, $existing)) {
			$existing[$id] = null;
		} else {
			$pav = new ProductHasAttributeValue();
			$pav->product = $product;
			$pav->value = $id;
			$pavs[] = $pav;
		}
	}
	if ($pavs) {
		ProductHasAttributeValue::bulk_save($pavs);
	}
	
	$delete = array();
	foreach ($existing as $id => $pav) {
		if ($pav) {
			$delete[] = $pav->id;
		}
	}
	
	if ($delete) {
		$query = "	DELETE
					FROM ot_product_has_attribute_value
					WHERE id IN (" . join(', ', $delete) . ")";
		
		if ($result = MySQL::query($query)) {
			
		}
	}
	
}

$redirect = $ot->get_link('products', $product->id);
header("Location: $redirect");