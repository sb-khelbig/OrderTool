<?php $attribute_set = AttributeSet::get($_GET['id']); ?>

<h1><?php echo $attribute_set->name; ?></h1>

<form id="attributes" action="<?php echo $ot->get_link('settings', $attribute_set->id, 'attributeset'); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="update" />

<?php foreach ($attribute_set->attributes->all() as $atr): ?>
	<fieldset>
		<legend><?php echo $atr->attribute->name; ?></legend>
		<?php //echo $atr_val->html(); ?>
	</fieldset>
<?php endforeach; ?>
</form>

<div id="add_attribute">
	<?php echo create_dropdown_menu('attribute', 'ot_attribute'); ?>
	<button id="add_attribute_button">Hinzufügen</button>
	<button id="save">Speichern</button>
</div>

<script>
	jQuery(document).ready(function () {
		var counter = 0;
		var attributes = $('#attributes');
		var add_attribute = $('#add_attribute');
		var select_attribute = $('select[name=attribute]', add_attribute);

		$('#add_attribute_button', add_attribute).bind('click', function () {
			var attribute_id = select_attribute.val();
			$.get('settings/ot_settings_attributeset_ajax.php',
					{action: 'attribute', id:  attribute_id},
					function (data) {
						if (data['error']) {
							alert(data['error']);
						} else {
							var atr = data['data'];
							var fs = $('<fieldset><legend>' + atr['name'] + '</legend></fieldset>');
							fs.append('<input type="hidden" name="new_atr[' + counter + ']" value="' + attribute_id + '" />');
							switch (atr['type']) {
								case '0':
									fs.append('<input type="text" name="new_value[' + counter + ']" />');
									break;
								case '1':
									fs.append('<input type="checkbox" name="new_value[' + counter + ']" value="1" />');
									break;
								case '2':
									fs.append('<textarea name="new_value[' + counter + ']"></textarea>');
									break;
								case '3':
									fs.append('<input type="number" name="new_value[' + counter + ']" />');
									break;
								case '4':
									var datetime = $('<input type="datetime" name="new_value[' + counter + ']" />');
									datetime.datepicker();
									fs.append(datetime);
									break;
								case '5':
									var select = $('<select name="new_value[' + counter + ']"></select>');
									$.each(atr['select'], function (index, value) {
										select.append('<option value="' + value['id'] + '">' + value['data'] + '</option>');
									});
									fs.append(select);
									break;
								default:
									break;
							}
							attributes.append(fs);
							counter++;
						}
					}, 'json');
		});

		$('#save', add_attribute).bind('click', function () {
			attributes.submit();
		});
	});
</script>