<?php
$tables = array(
		'ot_order' => 'Bestellung',
		'ot_position' => 'Position',
		'ot_customer' => 'Kunde',
		'ot_customer_address' => 'Adresse',
	);

$data = array();
foreach ($data_source->attributes->all() as $attribute) {
	$data[$attribute->ref_table][$attribute->id] = $attribute;
}
?>

<form action="<?php echo $ot->get_link('data_source', $data_source->id); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="api_save_atrs" />
	<?php foreach ($data as $ref_table => $entries): ?>
		<?php $attributes = Attribute::filter(array('ref_table' => $ref_table)); ?>
		<fieldset>
			<legend><?php echo $tables[$ref_table]; ?></legend>
			<table>
			<?php foreach ($entries as $id => $atr): ?>
				<tr>
					<td><?php echo $atr->field_title; ?></td>
					<td>
						<select name="<?php echo "atr[". $atr->id . "]"; ?>">
							<option value="0">Nicht speichern</option>
							<?php foreach ($attributes as $attribute): ?>
								<?php $selected = ($atr->attribute === $attribute) ? 'selected' : ''; ?>
								<option value="<?php echo $attribute->id; ?>" <?php echo $selected; ?>><?php echo $attribute->name; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
			</table>
		</fieldset>
	<?php endforeach; ?>
		<input type="submit" value="Speichern" />
</form>