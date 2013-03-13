<?php

$name = isset($_POST['name']) ? $_POST['name'] : false;

if ($name) {
	$attribute = new Attribute();
	$attribute->name = $name;
	$attribute->ref_table = 'ot_product';
	$attribute->save();
	
	$redirect = $ot->get_link('products', $attribute->id, 'attributes');
} else {
	$redirect = $ot->get_link('products', 0, 'attributes');
}

header("Location: $redirect");

