<?php
$attribute = Attribute::get($_GET['id']); 
$values = Value::filter(array('ref_id' => $attribute->id, 'attribute_id' => $attribute->id));
?>

<h1><?php echo $attribute->name; ?></h1>

<form action="<?php echo $ot->get_link('products', $attribute->id, 'attributes'); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="save" />
	<fieldset>
		<legend>Werte</legend>
		<div id="value-container">
			<?php if ($values): ?>
				<?php foreach ($values as $value): ?>
					<input type="text" name="values[<?php echo $value->id; ?>]" value="<?php echo $value->data; ?>" /> <br />
				<?php endforeach; ?>
				
			<?php else: ?>
				<p id="no_values">Keine Werte vorhanden!</p>
				
			<?php endif; ?>
		</div>
		<input type="button" id="add_value_button" value="Hinzufügen" />
		<input type="submit" value="Speichern" />
	</fieldset>
</form>

<script>
	jQuery(document).ready(function () {
		var container = $('#value-container');
		
		$('#add_value_button').bind('click', function () {
			var no_val = $('#no_values', container);
			if (no_val.length > 0) {
				no_val.remove();
			}
			container.append('<input type="text" name="new_values[]" /><br />');
		});
	});
</script>
