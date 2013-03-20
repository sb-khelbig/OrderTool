<?php $ticket = Ticket::get($_GET['id']); ?>

<h1>Ticket ID <?php echo $ticket->id; ?></h1>

<h2>Allgemeine Informationen</h2>
<form>
	<fieldset>
		<legend>Fragesteller</legend>
		<label for="inquirer_title">Anrede:</label>
		<select name="inquirer_title">
			<option value="1" <?php echo ($ticket->inquirer_title == 1) ? 'selected' : ''; ?>>Herr</option>
			<option value="2" <?php echo ($ticket->inquirer_title == 0) ? 'selected' : ''; ?>>Frau</option>
		</select> <br />
		<label for="inquirer_first_name">Vorname:</label>
		<input type="text" name="inquirer_first_name" value="<?php echo $ticket->inquirer_first_name; ?>" /> <br />
		<label for="inquirer_last_name">Nachname:</label>
		<input type="text" name="inquirer_last_name" value="<?php echo $ticket->inquirer_last_name; ?>" /> <br />
		<label for="inquirer_mail">Mail:</label>
		<input type="text" name="inquirer_mail" value="<?php echo $ticket->inquirer_mail; ?>" /> <br />
	</fieldset>
	<fieldset>
		<legend>Optionen</legend>
		<label for="ticket_category_id">Kategorie:</label>
		<?php echo TicketCategory::create_dropdown_menu('ticket_category_id', 'Wählen...', $ticket->category); ?> <br />
		<label for="status">Status:</label>
		<select name="status">
			<option value="0">Offen</option>
			<option value="1">Beantwortet</option>
			<option value="2">Geschlossen</option>
		</select> <br />
	</fieldset>
	<fieldset>
		<legend>Referenz</legend>
		<div id="info_tabs" style="font-size: 10pt;">
			<ul>
				<li><a href="#info_tabs-1">ID</a></li>
				<li><a href="#info_tabs-2">Bestellung</a></li>
				<li><a href="#info_tabs-3">Position</a></li>
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
				bla

			</div>
			<div id="info_tabs-3">
				bla
			</div>
		</div>
	</fieldset>
</form>


<h2>Korrespondenz</h2>

<button id="send_response_button">Antworten</button>
<button id="add_response_button">Kundenantwort einfügen</button>

<br />

<?php foreach ($ticket->entries->all() as $entry): ?>
<fieldset>
	<legend>
		<?php echo ($entry->response) ? "USER XYZ" : $ticket->inquirer_first_name . ' ' . $ticket->inquirer_last_name; ?> | <?php echo date('d.m.Y G:i', $entry->created) . ' Uhr'; ?>
	</legend>
	<textarea class="autoresize" style="width: 100%;"><?php echo $entry->text; ?></textarea>
</fieldset>
<?php endforeach; /*?>


<div id="send_response_dialog" style="font-size: 10pt">
	<form id="send_response_form" action="<?php echo $ot->get_link('ticket', $ticket->id); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="respond" />
		<div id="response_tabs" style="font-size: 10pt;">
			<ul>
				<li><a href="#response_tabs-1">Optionen</a></li>
				<li><a href="#response_tabs-2">Nachricht (Kunde)</a></li>
				<li><a href="#response_tabs-3" style="display: none;">Nachricht (Partner)</a></li>
				<li><a href="#response_tabs-4">Bestellung</a></li>
				<li><a href="#response_tabs-5">Liste</a></li>
			</ul>
			<div id="response_tabs-1">
				<label for="template">Vorlage:</label>
				<?php echo create_dropdown_menu('template', 'ot_mail_template', 'Keine Vorlage verwenden'); ?> <br />
				<label for="partner">Anbieter:</label>
				<select name="partner">
					<option value="0">Anbieter nicht informieren</option>
					<option value="1">Anbieter informieren</option>
				</select>
			</div>
			<div id="response_tabs-2">
				<textarea name="text_customer" rows="20" style="width: 100%;"><?php echo quote_mail(array_pop($entries), $ticket); ?></textarea>
			</div>
			<div id="response_tabs-3">
				<input type="text" name="partner_mail" placeholder="eMail Adresse"  style="width: 100%"/> <br />
				<textarea name="text_partner" rows="20" style="width: 100%;"></textarea>
			</div>
			<div id="response_tabs-4">
			<?php foreach ($order_data as $id => $data): ?>
				<label><?php echo ($data['name']) ? $data['name'] : 'Optional'; ?>:</label>
				<input type="text" value="<?php echo $data['data']; ?>" /> <br />
			<?php endforeach; ?>
			</div>
			<div id="response_tabs-5">
			<?php foreach ($order_list_data as $id => $data): ?>
				<label><?php echo $data['name']; ?>:</label>
				<input type="text" value="<?php echo $data['value']; ?>" /> <br />
			<?php endforeach; ?>
			</div>
		</div>
	</form>
</div>

<div id="add_response_dialog" style="font-size: 10pt">
	<form id="add_response_form" action="<?php echo $ot->get_link('ticket', $ticket['id']); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="addresponse" />
		<fieldset>
			<legend>Text</legend>
			<textarea name="text" rows="20" cols="80"></textarea>
		</fieldset>
	</form>
</div>
*/ ?>
<script type="text/javascript" src="static/tinymce/tiny_mce.js"></script>
<script>
	tinyMCE.init({
		mode : "textareas",
		theme : "simple"
	});

	$(document).ready(function () {
		$('#info_tabs').tabs({ heightStyle: "auto" });
		$('#response_tabs').tabs({ heightStyle: "auto" });
		
		$('#action-toggle').bind('click', function () {
			$checked = $(this).prop('checked');
			$('.action-selectbox', '.overview .table').prop('checked', $checked);
		});
	
		$('#send_response_button').bind('click', function () {
			$('#send_response_dialog').dialog('open');
		});
	
		$('#send_response_dialog').dialog({
			title: 'Kundenantwort',
			autoOpen: false,
			height: 'auto',
			width: '700px',
			modal: true,
			buttons: {
				'Senden': function () {
					$('#send_response_form').submit();
				}
			}
		});
		
		var form = $('#send_response_form');
		$('select[name=template]', form).bind('change', function () {
			var id = $(this).val();
			if (id > 0) {
				$.get('ticket/ot_ticket_ajax.php', {id: id, action: 'gettemplate'}, function (data) {
					if (data['success']) {
						$('textarea[name=text_customer]').val(data['data']['text']);
					} else {
						alert('Template konnte nicht geladen werden: ' + data['error']);
					}
				}, 'json');
			} else {
				$('textarea[name=text]').val('');
			}
		});

		$('#add_response_button').bind('click', function () {
			$('#add_response_dialog').dialog('open');
		});
	
		$('#add_response_dialog').dialog({
			title: 'Antwort',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Senden': function () {
					$('#add_response_form').submit();
				}
			}
		});
		
		$('select[name=partner]').bind('change', function (event) {
			var partner = $(this).val();
			if (partner > 0) {
				$('a[href="#response_tabs-3"]', '#response_tabs').show();
			} else {
				$('a[href="#response_tabs-3"]', '#response_tabs').hide();
			}
		});

		//$('textarea.autoresize').autosize();
	});
</script>