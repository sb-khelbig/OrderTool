<?php

$name = isset($_POST['name']) ? $_POST['name'] : false;

if ($name) {
	$supplier = new Supplier();
	$supplier->name = $name;
	$supplier->save();
}

$redirect = $ot->get_link('products', 0, 'suppliers');
header("Location: $redirect");