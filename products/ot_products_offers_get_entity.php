<?php $offer = Offer::get($_GET['id']);



if ($product = $offer->product) {
	
	$variants = array();
	foreach ($offer->variants->all() as $variant) {
		$variants[$variant->variant->id]['ohv'] = $variant->id;
	}
	
	$variants_headers = array();
	if ($vars = $product->variants->all()) {
		foreach ($vars as $variant) {
			foreach ($variant->pavs->all() as $pav) {
				$atr_id = $pav->pav->value->attribute->id;
				if (!array_key_exists($atr_id, $variants_headers)) {
					$variants_headers[$atr_id] = Attribute::get($atr_id);
				}
				$variants[$variant->id][$atr_id] = $pav->pav->value->data;
			}
		}
	}
} ?>

<h1>Angebots ID <?php echo $offer->id; ?></h1>

<form action="<?php echo $ot->get_link('products', $offer->id, 'offers'); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="save" />
	<fieldset>
		<legend>Einstellungen</legend>
		
		<label for="supplier">Anbieter</label>
		<select name="supplier">
			<option value="0">Wählen...</option>
			<?php foreach (Supplier::all() as $id => $supplier): ?>
				<?php $selected = ($supplier === $offer->supplier) ? 'selected' : ''; ?>
				<option value="<?php echo $id; ?>" <?php echo $selected; ?>><?php echo $supplier->name; ?></option>
			<?php endforeach; ?>
		</select> <br />
		
		<label for="product">Produkt</label>
		<select name="product">
			<option value="0">Wählen...</option>
			<?php foreach (Product::all() as $id => $product): ?>
				<?php $selected = ($product === $offer->product) ? 'selected' : ''; ?>
				<option value="<?php echo $id; ?>" <?php echo $selected; ?>><?php echo $product->name; ?></option>
			<?php endforeach; ?>
		</select> <br />
		
	</fieldset>
	
	<?php if ($variants): ?>
		<fieldset>
			<legend>Varianten</legend>
			<table id="variants-container">
				<thead>
					<tr>
						<th><input type="checkbox" /></th>
						<?php foreach ($variants_headers as $atr): ?>
							<th><?php echo $atr->name; ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($variants as $var_id => $variant): ?>
						<tr>
							<?php if (isset($variant['ohv'])): ?>
								<td><input type="checkbox" name="<?php echo "variants[$var_id]"; ?>" value="<?php echo $variant['ohv']; ?>" checked="checked" /></td>
							<?php else: ?>
								<td><input type="checkbox" name="<?php echo "variants[$var_id]"; ?>" value="0" /></td>
							<?php endif; ?>
							
							<?php foreach ($variants_headers as $atr_id => $name): ?>
								<td><?php echo $variant[$atr_id]; ?></td>
							<?php endforeach;?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</fieldset>
	<?php endif; ?>
	
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
			<?php foreach ($offer->matching->all() as $ohds): ?>
				<tr>
					<td><?php echo $ohds->data_source->name; ?></td>
					<td><input type="text" name="ohds[<?php echo $ohds->id;?>]" value="<?php echo $ohds->external_id; ?>" /></td>
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
			tr.append('<td><input type="text" name="new_ohds[]" /></td>');
			data_source_matching.append(tr);
			$('#data_source_table').show();
		});
	});
</script>
