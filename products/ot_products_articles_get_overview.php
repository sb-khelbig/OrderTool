<?php
$data = array(
		'module' => 'products',
		'sub' => 'articles',
		'title' => 'Artikel',
		'title_plural' => 'Artikel',
	);

$fields = array(
		'id' => array(
				'title' => 'ID',
				'link' => TRUE,
			),
	);

$objects = Article::all(); ?>

<?php include 'functions/ot_show_overview.php'; ?>

<button id="add">Hinzufügen</button>

<div id="add_dialog">
	<form id="add_form" action="<?php $ot->get_link('products', 0, 'articles'); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="add" />
	</form>
</div>

<?php include 'functions/ot_add_script.php'; ?>