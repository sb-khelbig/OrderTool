<?php $product = Product::get($_GET['id']);

$attributes = array();
foreach ($product->attributes->all() as $pav) {
	$attribute = $pav->value->attribute;
	$attributes[$attribute->id][$pav->value->id] = array('value' => $pav->value->data, 'checked' => 'checked="checked"');
}

if ($attributes) {
	foreach (Value::filter(array('attribute_id' => array_keys($attributes))) as $value) {
		$attribute = $value->attribute;
		if (!array_key_exists($value->id, $attributes[$attribute->id])) {
			$attributes[$attribute->id][$value->id] = array('value' => $value->data, 'checked' => '');
		}
	}
} 

$variants = array();
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
} ?>

<h1><?php echo $product->name; ?></h1>

<form action="<?php echo $ot->get_link('products', $product->id); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="save" />
	<fieldset>
		<legend>Attribute</legend>
		<div id="attributes-container">
		<?php if ($attributes): ?>
			<?php foreach ($attributes as $atr_id => $values): ?>
				<fieldset>
					<legend><?php echo Attribute::get($atr_id)->name; ?></legend>
					<?php foreach ($values as $val_id => $value): ?>
						<input type="checkbox" name="<?php echo "value[$val_id]"; ?>" value="1" <?php echo $value['checked']; ?> />
						<?php echo $value['value']; ?> <br />
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
			
		<?php else: ?>
			<p id="no_attributes">Keine Attribute vorhanden!</p>
			
		<?php endif; ?>
		</div>
		<select id="add_attribute_select">
			<?php foreach (Attribute::filter(array('ref_table' => 'ot_product')) as $attribute): ?>
				<option value="<?php echo $attribute->id; ?>"><?php echo $attribute->name; ?></option>
			<?php endforeach; ?>
		</select>
		<input id="add_attribute_button" type="button" value="Hinzufügen" />
		<input type="submit" value="Speichern" />
	</fieldset>
</form>

<form action="<?php echo $ot->get_link('products', $product->id); ?>" method="POST" enctype="multipart/form-data" <?php if (!$attributes) echo 'style="display: none;"'; ?>>
	<input type="hidden" name="action" value="variants" />
	<fieldset>
		<legend>Varianten</legend>
		<input type="submit" value="Varianten generieren" />
		<?php if ($variants): ?>
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
							<td><input type="checkbox" name="<?php echo "variants[$var_id]"; ?>" value="1" /></td>
							<?php foreach ($variants_headers as $atr_id => $name): ?>
								<td><?php echo $variant[$atr_id]; ?></td>
							<?php endforeach;?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</fieldset>
</form>


<script>
	jQuery(document).ready(function () {
		var select = $('#add_attribute_select');
		var atr_container = $('#attributes-container');
		
		$('#add_attribute_button').bind('click', function () {
			var id = select.val();
			$.get(
					'products/ot_products_ajax.php',
					{action: 'attribute', id: id},
					function (response) {
						if (!response['error']) {
							var no = $('#no_attributes', atr_container);
							if (no.length > 0) {
								no.remove();
							}
							var fieldset = $('<fieldset>');
							fieldset.append('<legend>' + response['data']['attribute']['name'] + '<legend>');
							$.each(response['data']['values'], function (id, value) {
								fieldset.append('<input type="checkbox" name="value[' + id + ']" value="1" />');
								fieldset.append(value + '<br />');
							});
							atr_container.append(fieldset);
						} else {
							alert(response['msg']);
						}
					},
					'json'
				);
		});

	});
</script>