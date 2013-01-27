<?php include 'ot_import_action_data.php'; ?>

<h1><?php echo $import['file_name']; ?></h1>

<form action="index.php?p=import" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="has_header" value="<?php echo $has_header; ?>" />
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="id" value="<?php echo $import['id']; ?>" />
	
	<div id="settings">
	<h2>Einstellungen</h2>
		<table>
			<tr>
				<td>Liste</td>
				<td><?php echo create_dropdown_menu('list', 'ot_order_list', 'Liste wählen'); ?></td>
			</tr>
			<tr>
				<td>Matching</td>
				<td>
					<select name="matching" disabled style="width: 200px;">
						<option value="0">Kein Matching</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Status</td>
				<td><?php echo create_dropdown_menu('status', 'ot_order_status', 'Kein Status'); ?></td>
			</tr>
		</table>
	</div>
	
	<div id="header">
		<h2>Daten</h2>
		<?php include 'ot_import_action_table.php'; ?>
	</div>
	
	<input type="submit" value="Datei speichern" />
</form>

<script type="text/javascript">
	function catch_submit(obj) {
		var optional = 0;
		$('select[name^=headers]').each(function (pos, header) {
			if ($(header).val() == 0) optional++;
		});
		switch (optional) {
			case 0:
				return true;
			case 1:
				return confirm('Für eine Spalte wurde kein Header ausgewählt. Datei wirklich speichern?');
			default:
				return confirm('Für '+optional+' Spalten wurden keine Header ausgewählt. Datei wirklich speichern?');
		}
	};

	function add_options(options) {
		var select = $("select[name='matching']");
		$.each(options, function (key, value) {
			var opt = new Array('<option value="', key, '">', value, '</option>');
			$(opt.join('')).appendTo(select);
		});
	};

	function select_headers(headers) {
		var regex = new RegExp('[0-9]+');
		jQuery('.header select').each(function (pos, obj) {
			var select = $(obj);
			var name = select.attr('name');
			var pos = regex.exec(name);
			if (headers[pos]) {
				select.val(headers[pos]);
			} else {
				select.val(0);
			}
		});
	};
	
	function load_matching_options(elem) {
		var id = $(this).children(':selected').attr('value');
		var select = $("select[name='matching']");
		if (id != '0') {
			select.children('*:not([value="0"])').remove();
			select.removeAttr('disabled');
			jQuery.get('ajax/ajax_import.php',
					{action: 'list', id: id},
					add_options,
					'json');
			jQuery.get('ajax/ajax_import.php',
					{action: 'headers', id: id},
					select_headers,
					'json');
		}
		if (id == '0') {
			select.attr('disabled', 'disabled');
		}
	};
	
	$(document).ready(function () {
		$("select[name='list']").bind('change', load_matching_options);
		$('form').bind('submit', catch_submit);
	});
</script>
