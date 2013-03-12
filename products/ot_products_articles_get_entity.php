<?php $article = Article::get($_GET['id']); ?>

<h1>Artikel ID <?php echo $article->id; ?></h1>

<form action="<?php echo $ot->get_link('products', $article->id, 'articles'); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="save" />
	<fieldset>
		<legend>Einstellungen</legend>
		<label for="product">Produkt</label>
		<select name="product">
			<option value="0">Wählen...</option>
			<?php foreach (Product::all() as $id => $product): ?>
				<?php $selected = ($product === $article->product) ? 'selected' : ''; ?>
				<option value="<?php echo $id; ?>" <?php echo $selected; ?>><?php echo $product->name; ?></option>
			<?php endforeach; ?>
		</select> <br />
		
		<label for="supplier">Anbieter</label>
		<select name="supplier">
			<option value="0">Wählen...</option>
			<?php foreach (Supplier::all() as $id => $supplier): ?>
				<?php $selected = ($supplier === $article->supplier) ? 'selected' : ''; ?>
				<option value="<?php echo $id; ?>" <?php echo $selected; ?>><?php echo $supplier->name; ?></option>
			<?php endforeach; ?>
		</select> <br />
	</fieldset>
	<fieldset>
		<legend>Datenquellen</legend>
		<table id="data_source_table" style="display: none;">
			<thead>
				<tr>
					<th>Quelle</th>
					<th>Externe ID</th>
				</tr>
			</thead>
			<tbody id="data_source_matching">
			<?php foreach ($article->matching->all() as $ahds): ?>
				<tr>
					<td><?php echo $ahds->data_source->name; ?></td>
					<td><input type="text" name="ahds[<?php echo $ahds->id;?>]" value="<?php echo $ahds->external_id; ?>" /></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<input id="data_source_matching_add" type="button" value="Hinzufügen" />
	</fieldset>
	<input type="submit" value="Speichern" />
</form>

<select id="data_source_template" name="new_data_source[]" style="display: none;">
	<?php foreach (DataSource::all() as $id => $data_source): ?>
		<option value="<?php echo $id; ?>"><?php echo $data_source->name; ?></option>
	<?php endforeach; ?>
</select>

<script>
	jQuery(document).ready(function () {
		var data_source_matching = $('#data_source_matching');
		var data_source_template = $('#data_source_template');

		if (data_source_matching.children().length > 0) {
			$('#data_source_table').show();
		};
		
		$('#data_source_matching_add').bind('click', function () {
			var tr = $('<tr>');
			var td = $('<td>');
			td.append(data_source_template.clone().show());
			tr.append(td);
			tr.append('<td><input type="text" name="new_ahds[]" /></td>');
			data_source_matching.append(tr);
			$('#data_source_table').show();
		});
	});
</script>
