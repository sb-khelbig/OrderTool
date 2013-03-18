<?php

function build_variants($value, $variant, &$variants) {
	if ($vars = array_pop($value)) {
		foreach ($vars as $index => $var) {
			$current = $variant;
			$current[] = $var;
			build_variants($value, $current, $variants);
		}
	} else {
		$variants[] = $variant;
	}
}

$product = Product::get($_GET['id']);

$attributes = array();
foreach ($product->attributes->all() as $pav) {
	$attributes[$pav->value->attribute->id][$pav->value->id] = $pav;
}

$variants = array();
build_variants($attributes, array(), $variants);

$vars = array();
foreach ($variants as $variant) {
	$var = new ProductVariant();
	$var->product = $product;
	foreach ($variant as $value) {
		$phv = new ProductVariantHasPAV();
		$phv->pav = $value;
		$var->pavs->add($phv);
	}
	$vars[] = $var;
}

ProductVariant::bulk_save($vars);

$redirect = $ot->get_link('products', $product->id);
header("Location: $redirect");

