<?php

print_r($_POST);

$attribute_set = AttributeSet::get($_GET['id']);

if ($new_atr_ids = isset($_POST['new_atr']) ? $_POST['new_atr'] : FALSE) {
	// Caching
	Attribute::filter(array('id' => $new_atr_ids));
	
	//$new_values = isset($_POST['new_value']) ? $_POST['new_value'] : array();
	
	$tosave = array();
	foreach ($new_atr_ids as $index => $atr_id) {
		$set_has_val = new AttributeSetHasAttribute();
		$set_has_val->attribute_set = $attribute_set;
		$set_has_val->attribute = Attribute::get($atr_id);
		$tosave[] = $set_has_val;
	}
	AttributeSetHasAttribute::bulk_save($tosave);
}


/*
if (isset($_POST['attributes'])) {
	foreach ($_POST['attributes'] as $id => $value) {
		$atr = $attributes->get($id);
		$atr->change($value);
	}
}*/

$redirect = $ot->get_link('settings', $attribute_set->id, 'attributeset');
header("Location: $redirect");