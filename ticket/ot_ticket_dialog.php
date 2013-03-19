<div class="create_ticket_dialog" id="create_ticket_dialog" style="font-size: 10pt">
	<form id="create_ticket_form" action="<?php echo $ot->get_link('ticket'); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="create" />
		<fieldset>
			<legend>Fragesteller</legend>
			<label for="inquirer_title">Anrede:</label>
			<select name="inquirer_title">
				<option value="1">Herr</option>
				<option value="2">Frau</option>
			</select> <br />
			<label for="inquirer_first_name">Vorname:</label>
			<input type="text" name="inquirer_first_name" /> <br />
			<label for="inquirer_last_name">Nachname:</label>
			<input type="text" name="inquirer_last_name" /> <br />
			<label for="inquirer_mail">Mail:</label>
			<input type="text" name="inquirer_mail" /> <br />
		</fieldset>
		<fieldset>
			<legend>Optionen</legend>
			<label for="ticket_category_id">Kategorie:</label>
			<?php echo TicketCategory::create_dropdown_menu('ticket_category_id', 'Unbekannt'); ?> <br />
			<label for="status">Status:</label>
			<select name="status">
				<option value="0">Offen</option>
				<option value="1">Beantwortet</option>
				<option value="2">Geschlossen</option>
			</select> <br />
		</fieldset>
		<fieldset>
			<legend>Referenz</legend>
			<label for="ref_table">Anfrage zu:</label>
			<select name="ref_table">
				<option value="ot_order">Bestellung</option>
				<option value="ot_position">Position</option>
			</select>
			<input type="number" name="ref_id" />
		</fieldset>
		<fieldset>
			<legend>Text</legend>
			<textarea name="text" rows="20" cols="80"></textarea>
		</fieldset>
	</form>
</div>