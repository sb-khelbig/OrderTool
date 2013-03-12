<?php $product = Product::get($_GET['id']); ?>

<h1><?php echo $product->name; ?></h1>

