<?php
$data = array(
		'module' => 'products',
		'sub' => 'attributes',
		'title' => 'Attribut',
		'title_plural' => 'Attribute',
	);

$fields = array(
		'id' => array(
				'title' => 'ID',
				'link' => FALSE,
			),
		'name' => array(
				'title' => 'Name',
				'link' => TRUE,
			),
	);

$objects = Attribute::filter(array('ref_table' => 'ot_product')); ?>

<?php include 'functions/ot_show_overview.php'; ?>

<button id="add">Hinzufügen</button>

<div id="add_dialog">
	<form id="add_form" action="<?php $ot->get_link('products', 0, 'articles'); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="add" />
		<input type="text" name="name" />
	</form>
</div>

<?php include 'functions/ot_add_script.php'; ?>