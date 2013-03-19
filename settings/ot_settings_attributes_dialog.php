<div id="add_attribute">
	<form id="add_attribute_form" action="<?php echo $ot->get_link('settings', 0, 'attributes'); ?>" method="POST" enctype="multipart/form-data" >
		<input type="hidden" name="action" value="add" />
		<fieldset>
			<label for="name">Name: </label>
			<input type="text" name="name" /> <br />
			<label for="ref_table">Tabelle: </label>
			<select name="ref_table">
				<?php foreach (Table::all() as $table => $class): ?>
					<option value="<?php echo $table; ?>"><?php echo $class::getTitle(FALSE); ?></option>
				<?php endforeach; ?>
			</select> <br />
			<label for="type">Typ: </label>
			<select name="type">
				<option value="0">Textzeile</option>
				<option value="1">Checkbox</option>
				<option value="2">Textfeld</option>
				<option value="3">Zahl</option>
				<option value="4">Datum</option>
				<option value="5">Auswahl</option>
			</select> <br />
		</fieldset>
		<fieldset id="add_attribute_select" style="display: none;">
			<legend>Auswahl</legend>
			<div id="add_attribute_select_options">
					<input type="text" name="select[]" /> <br />
					<input type="text" name="select[]" /> <br />
			</div>
			<input id="add_attribute_select_add_option" type="button" value="Option hinzufügen" />
		</fieldset>
	</form>
</div>

<script>
	function register_add_attribute (button) {
		var add_attribute = $('#add_attribute');
		
		add_attribute.dialog({
			title: 'Attribut anlegen',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Anlegen': function () {
					$('#add_attribute_form', add_attribute).submit();
				}
			}
		});

		button.bind('click', function () {
			add_attribute.dialog('open');
		});

		var add_attribute_select = $('#add_attribute_select', add_attribute);
		var add_attribute_select_options = $('#add_attribute_select_options', add_attribute_select);
		
		$('#add_attribute_select_add_option').bind('click', function () {
			add_attribute_select_options.append('<input type="text" name="select[]" /> <br />');
		});

		$('select[name=type]', add_attribute).bind('change', function () {
			if ($(this).val() == '5') {
				add_attribute_select.show();
			} else {
				add_attribute_select.hide();
			}
		});
	};
</script>