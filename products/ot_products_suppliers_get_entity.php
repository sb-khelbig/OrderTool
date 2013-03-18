<?php $supplier = Supplier::get($_GET['id']); ?>

<h1><?php echo $supplier->name; ?></h1>

