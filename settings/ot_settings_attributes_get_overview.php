<h1>Attribute</h1>

<table>
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Tabelle</th>
		<th>Typ</th>
	</tr>
<?php foreach (Attribute::all() as $attribute): ?>
	<tr>
		<td><?php echo $attribute->id; ?></td>
		<td><a href="<?php echo $ot->get_link('settings', $attribute->id, 'attributes'); ?>"><?php echo $attribute->name; ?></a></td>
		<td><?php echo $attribute->ref_table; ?></td>
		<td><?php echo $attribute->type; ?></td>
	</tr>
<?php endforeach; ?>
</table>

<button id="add_attribute_button">Attribut anlegen</button>

<?php include 'settings/ot_settings_attributes_dialog.php'; ?>

<script>
	jQuery(document).ready(function () {
		register_add_attribute($('#add_attribute_button'));
	});
</script>