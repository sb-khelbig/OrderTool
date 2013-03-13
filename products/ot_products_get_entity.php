<?php $product = Product::get($_GET['id']);

$attributes = array();
foreach ($product->attributes->all() as $pav) {
	$attribute = $pav->value->attribute;
	$attributes[$attribute->id][$pav->value->id] = array('value' => $pav->value->data, 'checked' => TRUE);
}

if ($attributes) {
	foreach (Value::filter(array('attribute_id' => array_keys($attributes))) as $value) {
		$attribute = $value->attribute;
		if (!array_key_exists($value->id, $attributes[$attribute->id])) {
			$attributes[$attribute->id][$value->id] = array('value' => $value->data, 'checked' => FALSE);
		}
	}
} ?>

<h1><?php echo $product->name; ?></h1>

<form>
	<fieldset>
		<legend>Attribute</legend>
		<?php if ($attributes): ?>
			<?php foreach ($attributes as $atr_id => $values): ?>
				<fieldset>
					<legend><?php echo Attribute::get($atr_id)->name; ?></legend>
					<?php foreach ($values as $val_id => $value): ?>
						<?php $checked = ($value['checked']) ? 'checked' : ''; ?>
						<input type="checkbox" name="<?php echo "value[$val_id]"; ?>" value="1" <?php echo $checked; ?> />
						<?php echo $value['value']; ?> <br />
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
			
		<?php else: ?>
			<p>Keine Attribute vorhanden!</p>
			
		<?php endif; ?>
	</fieldset>
</form>

<?php if ($attributes): ?>
	<form>
		<fieldset>
			<legend>Varianten</legend>
		</fieldset>
	</form>
<?php endif; ?>