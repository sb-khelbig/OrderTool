<?php
$data = array(
		'module' => 'products',
		'title' => 'Produkt',
		'title_plural' => 'Produkte',
	);

$fields = array(
		'id' => array(
				'title' => 'ID',
				'link' => FALSE),
		'name' => array(
				'title' => 'Name',
				'link' => TRUE),
	);

$objects = Product::all(); ?>

<?php include 'functions/ot_show_overview.php'; ?>

<button id="add">Hinzufügen</button>

<div id="add_dialog">
	<form id="add_form" action="<?php $ot->get_link('products'); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="add" />
		<label for="name">Name</label>
		<input type="text" name="name" />
	</form>
</div>

<?php include 'functions/ot_add_script.php'; ?>