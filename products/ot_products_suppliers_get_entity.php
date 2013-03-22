<?php $supplier = Supplier::get($_GET['id']); ?>

<h1><?php echo $supplier->name; ?></h1>

<form>
	<fieldset>
		<legend>Kontakte</legend>
		<?php if ($contacts = $supplier->contacts->all()): ?>
			<ul>
			<?php foreach ($contacts as $contact): ?>
				<li>
					<span><?php echo $contact->title() . ' ' . $contact->first_name . ' ' . $contact->last_name; ?></span> <br />
					<span><?php echo $contact->mail; ?></span>
				</li>
			<?php endforeach; ?>
			</ul>
			
		<?php else: ?>
			<p>Keine Kontakte vorhanden!</p>
			
		<?php endif; ?>
		<input id="add_contact_button" type="button" value="Hinzufügen" />
	</fieldset>
</form>

<div id="add_contact_dialog">
	<form action="<?php echo $ot->get_link('products', $supplier->id, 'suppliers'); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="add_contact" />
		<?php echo Contact::create_dropdown_menu('contact', 'Neu'); ?>
		<div id="new_contact">
			<label for="title">Anrede:</label>
			<select name="title">
				<option value="1">Herr</option>
				<option value="2">Frau</option>
			</select> <br />
			<label for="first_name">Vorname:</label>
			<input type="text" name="first_name" /> <br />
			<label for="last_name">Nachname:</label>
			<input type="text" name="last_name" /> <br />
			<label for="mail">Mail:</label>
			<input type="text" name="mail" /> <br />
		</div>
	</form>
</div>

<script>
	jQuery(document).ready(function () {
		var dialog = $('#add_contact_dialog');
		
		$('#add_contact_button').bind('click', function () {
			dialog.dialog('open');
		});

		dialog.dialog({
			title: 'Kontakt',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Hinzufügen': function () {
					$('form', dialog).submit();
				}
			}
		});

		$('select[name=contact]', dialog).bind('change', function () {
			var select = $(this);

			if (select.val() == 0) {
				$('#new_contact').show();
			} else {
				$('#new_contact').hide();
			}
		});
	});
</script>