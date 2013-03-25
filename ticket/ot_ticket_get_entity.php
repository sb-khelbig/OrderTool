<?php $ticket = Ticket::get($_GET['id']);

$inquirer = TicketParticipant::filter(array('ticket_id' => $ticket, 'type' => 1));
$inquirer = array_pop($inquirer); 

if ($ticket->ref_table == 'ot_position') {
	$references = $ticket->references->all();
	$position = Position::get($references[0]->ref_id);
	$order = $position->order;
} else {
	$references = $ticket->references->all();
	$order = Order::get($references[0]->ref_id);
}

$position_ids = array();
foreach ($order->positions->all() as $position) {
	$position_ids[] = $position->id;
}

$values = Value::filter(array(
		'ref_id' => $position_ids,
		'attribute_id' => 14,
	)
); ?>

<h1>Ticket ID <?php echo $ticket->id; ?></h1>

<h2>Allgemeine Informationen</h2>
<form>
	<?php if ($inquirer): ?>
		<fieldset>
			<legend>Fragesteller</legend>
			<label for="inquirer_title">Anrede:</label>
			<?php echo $inquirer->title('inquirer_title'); ?> <br />
			<label for="inquirer_first_name">Vorname:</label>
			<input type="text" name="inquirer_first_name" value="<?php echo $inquirer->first_name; ?>" /> <br />
			<label for="inquirer_last_name">Nachname:</label>
			<input type="text" name="inquirer_last_name" value="<?php echo $inquirer->last_name; ?>" /> <br />
			<label for="inquirer_mail">Mail:</label>
			<input type="text" name="inquirer_mail" value="<?php echo $inquirer->mail; ?>" /> <br />
		</fieldset>
	<?php endif; ?>
	<fieldset>
		<legend>Optionen</legend>
		<label for="ticket_category_id">Kategorie:</label>
		<?php echo TicketCategory::create_dropdown_menu('ticket_category_id', 'Wählen...', $ticket->category); ?> <br />
		<label for="status">Status:</label>
		<select name="status">
			<option value="0">Offen</option>
			<option value="1">In Bearbeitung</option>
			<option value="2">Beantwortet</option>
			<option value="3">Geschlossen</option>
		</select> <br />
	</fieldset>
	<fieldset>
		<legend>Referenz</legend>
		<div id="info_tabs" style="font-size: 10pt;">
			<ul>
				<li><a href="#info_tabs-1">ID</a></li>
				<li><a href="#info_tabs-2">Bestellung</a></li>
				<li><a href="#info_tabs-3">Positionen (<?php echo count($order->positions->all()); ?>)</a></li>
			</ul>
			<div id="info_tabs-1">
				<label for="ref_table">Anfrage zu:</label>
				<select name="ref_table">
					<option value="ot_order" <?php echo ($ticket->ref_table == 'ot_order') ? 'selected' : ''; ?>>Bestellung</option>
					<option value="ot_position" <?php echo ($ticket->ref_table == 'ot_position') ? 'selected' : ''; ?>>Position</option>
				</select>
				<ul>
				<?php foreach ($ticket->references->all() as $reference): ?>
					<li><input type="number" name="ref_id" value="<?php echo $reference->ref_id; ?>" /></li>
				<?php endforeach; ?>
				</ul>
			</div>
			<div id="info_tabs-2">
				<ul>
					<?php foreach($order->attributes->all() as $atr): ?>
						<li><?php echo $atr->attribute->name . ': ' . $atr->data; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div id="info_tabs-3">
				<?php 
				$attributes = array('ID' => 'ID');
				$rows = array();
				foreach ($order->positions->all() as $entity) {
					$rows[$entity->id]['ID'] = $entity->id;
					foreach ($entity->attributes->all() as $atr) {
						if (!array_key_exists($atr->attribute->id, $attributes)) {
							$attributes[$atr->attribute->id] = $atr->attribute->short_name;
						}
						$rows[$entity->id][$atr->attribute->id] = $atr->data;
					}
				} ?>
				<table style="font-size: 12px;">
					<thead>
						<tr>
						<?php foreach ($attributes as $atr_id => $atr_name): ?>
							<th><?php echo $atr_name; ?></th>
						<?php endforeach; ?>
						</tr>
					</thead>
					
					<tbody>
						<?php foreach ($rows as $entity_id => $row): ?>
							<tr>
								<?php foreach ($attributes as $atr_id => $atr_name): ?>
									<?php if (array_key_exists($atr_id, $row)): ?>
						 				<td><?php echo $row[$atr_id]; ?></td>
						 			<?php else: ?>
										<td>&nbsp;</td>
									<?php endif; ?>
								<?php endforeach;?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</fieldset>
</form>
<form>
	<fieldset>
		<legend>Teilnehmer</legend>
			<table>
				<thead>
					<tr>
						<th>Typ</th>
						<th>Anrede</th>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>eMail</th>
					</tr>
				</thead>
				<tbody id="participants-table">
				<?php foreach ($ticket->participants->all() as $participant): ?>
					<tr>
						<td><?php echo $participant->type(); ?></td>
						<td><?php echo $participant->title(); ?></td>
						<td><?php echo $participant->first_name; ?></td>
						<td><?php echo $participant->last_name; ?></td>
						<td><?php echo $participant->mail; ?>
					<tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		 <input id="participants-button" type="button" value="Hinzufügen" />
	</fieldset>
</form>


<h2>Korrespondenz</h2>

<div style="margin-bottom: 5px;">
	<button id="send_response_button">Antworten</button>
	<button id="add_response_button">Kundenantwort einfügen</button>
</div>

<?php foreach (array_reverse($ticket->entries->all(), TRUE) as $entry): ?>
	<fieldset>
		<?php if ($entry->participant): ?>
			<legend>
				<?php echo $entry->participant->first_name . ' ' . $entry->participant->last_name; ?> | <?php echo date('d.m.Y G:i', $entry->created) . ' Uhr'; ?>
			</legend>
		<?php endif; ?>
		<textarea class="autoresize" style="width: 100%;"><?php echo $entry->text; ?></textarea>
	</fieldset>
<?php endforeach; ?>


<div id="send_response_dialog" style="font-size: 10pt">
	<fieldset>
		<legend>Sichtbarkeit</legend>
		<div>
			<label for="selector">Auswählen:</label>
			<select name="selector">
				<option value="<?php echo $inquirer->id; ?>">Kunde</option>
				<?php if ($partners = TicketParticipant::filter(array('ticket_id' => $ticket, 'type' => 0))): ?>
				<optgroup label="Partner">
					<?php foreach ($partners as $partner): ?>
						<option value="<?php echo $partner->id; ?>"><?php echo $partner->last_name; ?></option>
					<?php endforeach; ?>
				</optgroup>
				<?php endif; ?>
				<option>Mitarbeiter</option>
			</select>
			<input type="button" value="Markieren" />
		</div>
		<table style="font-size: 12px;">
			<thead>
				<tr>
					<th><input type="checkbox" /></th>
					<th>Teilnehmer</th>
					<th>Name</th>
					<th>Uhrzeit</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($ticket->entries->all() as $entry): ?>
				<tr>
					<td><input type="checkbox" name="<?php echo "entry[$entry]"; ?>" value="<?php echo $entry->participant->id; ?>" /></td>
					<td><?php echo $entry->participant->type(); ?></td>
					<td><?php echo $entry->participant->first_name . ' ' . $entry->participant->last_name; ?></td>
					<td><?php echo date('d.m.Y G:i', $entry->created) . ' Uhr'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
	<fieldset>
		<legend>Nachricht</legend>
		<div style="margin: 2px;">Template: <?php echo MailTemplate::create_dropdown_menu('template', 'Freitext'); ?></div>
		<textarea name="message" style="width: 100%;"></textarea>
	</fieldset>
</div>


 
<div id="add_entry_dialog" style="font-size: 10pt">
	<form id="add_entry_form" action="<?php echo $ot->get_link('ticket', $ticket->id); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="addresponse" />
		<fieldset>
			<legend>Text</legend>
			<textarea name="text" rows="20" cols="80"></textarea>
		</fieldset>
	</form>
</div>

<div id="participants-dialog" style="font-size: 12px">
	<form id="participants-form">
		<fieldset>
			<legend>Teilnehmer</legend>
			<select name="type">
				<option value="0">Wählen...</option>
				<?php if ($suppliers): ?>
					<?php foreach ($suppliers as $supplier): ?>
						<optgroup label="<?php echo "$supplier"; ?>">
							<?php foreach ($supplier->contacts->all() as $contact): ?>
								<option value="<?php $contact->id; ?>"><?php echo "$contact (" . $contact->mail . ")"; ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				<?php endif; ?>
				<option value="2">Extern</option>
			</select> <br />
			<label for="title">Anrede:</label>
			<select name="title" disabled>
				<option value="1">Herr</option>
				<option value="2">Frau</option>
			</select> <br />
			<label for="first_name">Vorname:</label>
			<input type="text" name="first_name" disabled /> <br />
			<label for="last_name">Nachname:</label>
			<input type="text" name="last_name" disabled /> <br />
			<label for="mail">Mail:</label>
			<input type="text" name="mail" disabled /> <br />
		</fieldset>
		<fieldset>
			<legend>Korrespondenz</legend>
			<fieldset>
				<legend>Zugriff erlauben</legend>
					<div>
						<div>
							<label for="selector">Auswählen:</label>
							<select name="selector">
								<option value="<?php echo $inquirer->id; ?>">Kunde</option>
								<?php if ($partners = TicketParticipant::filter(array('ticket_id' => $ticket, 'type' => 0))): ?>
								<optgroup label="Partner">
									<?php foreach ($partners as $partner): ?>
										<option value="<?php echo $partner->id; ?>"><?php echo $partner->last_name; ?></option>
									<?php endforeach; ?>
								</optgroup>
								<?php endif; ?>
								<option>Mitarbeiter</option>
							</select>
							<input type="button" value="Markieren" />
						</div>
						<table style="font-size: 12px;">
							<thead>
								<tr>
									<th><input type="checkbox" /></th>
									<th>Teilnehmer</th>
									<th>Name</th>
									<th>Uhrzeit</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($ticket->entries->all() as $entry): ?>
								<tr>
									<td><input type="checkbox" name="<?php echo "entry[$entry]"; ?>" value="<?php echo $entry->participant->id; ?>" /></td>
									<td><?php echo $entry->participant->type(); ?></td>
									<td><?php echo $entry->participant->first_name . ' ' . $entry->participant->last_name; ?></td>
									<td><?php echo date('d.m.Y G:i', $entry->created) . ' Uhr'; ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
			</fieldset>
			<fieldset>
				<legend>Nachricht <input id="participants-form-entry-switch" type="checkbox" /></legend>
				<div id="participants-form-text" style="display: none;"><textarea style="width: 500px; height: 200px;"></textarea></div>
			</fieldset>
		</fieldset>
	</form>
</div>

<script type="text/javascript" src="static/tinymce/tiny_mce.js"></script>
<script>
	tinyMCE.init({
		mode : "textareas",
		theme : "simple"
	});

	$(document).ready(function () {
		$('#info_tabs').tabs({ heightStyle: "auto" });
		$('#response_tabs').tabs({ heightStyle: "auto" });

		var send_response_dialog = $('#send_response_dialog');
		var send_response_form = $('#send_response_form', send_response_dialog);
		
		$('#send_response_button').bind('click', function () {
			send_response_dialog.dialog('open');
		});
	
		send_response_dialog.dialog({
			title: 'Kundenantwort',
			autoOpen: false,
			height: 'auto',
			width: '700px',
			modal: true,
			buttons: {
				'Senden': function () {
					send_response_form.submit();
				}
			}
		});
		
		
		$('select[name=template]', send_response_form).bind('change', function () {
			var id = $(this).val();
			var text = $('textarea[name=message]', send_response_form);
			if (id > 0) {
				$.get('ticket/ot_ticket_ajax.php', {id: id, action: 'gettemplate'}, function (data) {
					if (data['success']) {
						text.val(data['data']['text']);
					} else {
						alert('Template konnte nicht geladen werden: ' + data['error']);
					}
				}, 'json');
			} else {
				text.val('');
			}
		});

		var add_entry_dialog = $('#add_entry_dialog');

		$('#add_entry_button').bind('click', function () {
			add_entry_dialog.dialog('open');
		});
	
		add_entry_dialog.dialog({
			title: 'Antwort',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Senden': function () {
					$('#add_entry_form', add_entry_dialog).submit();
				}
			}
		});

		var participants = $('#participants-dialog');

		participants.dialog({
			title: 'Antwort',
			autoOpen: false,
			height: 'auto',
			width: '585',
			modal: true,
			buttons: {
				'Senden': function () {
					$('#participants-form').submit();
				}
			}
		});

		$('#participants-button').bind('click', function () {
			participants.dialog('open');
		});

		$('#participants-form-entry-switch').bind('change', function () {
			$('#participants-form-text').toggle();
		});
	});
</script>