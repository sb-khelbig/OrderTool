<?php
$name = isset($_POST['name']) ? $_POST['name'] : '';

if ($name) {
	$product = new Product();
	$product->name = $name;
	$product->save();
}

$redirect = $ot->get_link('products', $product->id);
header("Location: $redirect");