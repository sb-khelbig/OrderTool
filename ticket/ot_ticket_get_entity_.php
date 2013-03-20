<?php
$ticket = get_row_by_id($_GET['id'], 'ot_ticket') or die('ID existiert nicht!');

$result = mysql_query("	SELECT id, is_response, timestamp_created, text
						FROM ot_ticket_entry
						WHERE ticket_id = $ticket[id]
						ORDER BY timestamp_created DESC") or die('MySQLError: ' . mysql_error());
$entries = array();
while ($row = mysql_fetch_assoc($result)) {
	$entries[$row['id']] = $row;
}

$order_data = array();
$order_list_data = array();

/*
if ($ticket['ref_table'] == 'ot_row') {
	$result = mysql_query("	SELECT c.id, h.name, c.data
							FROM ot_row AS r
							JOIN ot_column AS c
								ON r.id = c.row_id
							JOIN ot_order_list_has_header AS olh
								ON r.order_list_id = olh.order_list_id
									AND olh.pos = c.pos
							LEFT JOIN ot_header AS h
								ON h.id = olh.header_id
							WHERE r.id = $ticket[ref_id]") or die('MySQLError: ' . mysql_error());
	$order_data = array();
	while ($row = mysql_fetch_assoc($result)) {
		$order_data[$row['id']] = $row;
	}
	$result = mysql_query("	SELECT ola.id, a.name, ola.value
							FROM ot_row AS r
							JOIN ot_order_list_has_attribute AS ola
								ON r.order_list_id = ola.order_list_id
							JOIN ot_attribute AS a
								ON a.id = ola.attribute_id
							WHERE r.id = $ticket[ref_id]") or die('MySQLError: ' . mysql_error());
	$order_list_data = array();
	while ($row = mysql_fetch_assoc($result)) {
		$order_list_data[$row['id']] = $row;
	}
} */

function quote_mail($mail, $ticket) {
	$sender = ($mail['is_response']) ? "USER XYZ" : "$ticket[inquirer_first_name] $ticket[inquirer_last_name]";
	$date = date('d.m.Y G:i', $mail['timestamp_created']) . ' Uhr';
	$text[] = "Am $date schrieb $sender:";
	$text[] = "<blockquote type=\"cite\">$mail[text]</blockquote>";
	return join('<br>', $text);
}
?>

<h1>Ticket ID <?php echo $ticket['id']; ?></h1>

<h2>Allgemeine Informationen</h2>
<form>
	<fieldset>
		<legend>Fragesteller</legend>
		<label for="inquirer_title">Anrede:</label>
		<select name="inquirer_title">
			<option value="1" <?php echo ($ticket['inquirer_title'] == 1) ? 'selected' : ''; ?>>Herr</option>
			<option value="2" <?php echo ($ticket['inquirer_title'] == 2) ? 'selected' : ''; ?>>Frau</option>
		</select> <br />
		<label for="inquirer_first_name">Vorname:</label>
		<input type="text" name="inquirer_first_name" value="<?php echo $ticket['inquirer_first_name']; ?>" /> <br />
		<label for="inquirer_last_name">Nachname:</label>
		<input type="text" name="inquirer_last_name" value="<?php echo $ticket['inquirer_last_name']; ?>" /> <br />
		<label for="inquirer_mail">Mail:</label>
		<input type="text" name="inquirer_mail" value="<?php echo $ticket['inquirer_mail']; ?>" /> <br />
	</fieldset>
	<fieldset>
		<legend>Optionen</legend>
		<label for="ticket_category_id">Kategorie:</label>
		<?php echo create_dropdown_menu('ticket_category_id', 'ot_ticket_category', 'Unbekannt', $ticket['ticket_category_id']); ?> <br />
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
					<option value="ot_order" <?php echo ($ticket['ref_table'] == 'ot_order') ? 'selected' : ''; ?>>Bestellung</option>
					<option value="ot_position" <?php echo ($ticket['ref_table'] == 'ot_position') ? 'selected' : ''; ?>>Position</option>
				</select>
				<input type="number" name="ref_id" value="<?php echo $ticket['ref_id']; ?>" />
			</div>
			<div id="info_tabs-2">
			<?php if?>
			<?php foreach ($order_data as $id => $data): ?>
				<label><?php echo ($data['name']) ? $data['name'] : 'Optional'; ?>:</label>
				<input type="text" value="<?php echo $data['data']; ?>" /> <br />
			<?php endforeach; ?>
			</div>
			<div id="info_tabs-3">
			<?php foreach ($order_list_data as $id => $data): ?>
				<label><?php echo $data['name']; ?>:</label>
				<input type="text" value="<?php echo $data['value']; ?>" /> <br />
			<?php endforeach; ?>
			</div>
		</div>
	</fieldset>
</form>

<h2>Korrespondenz</h2>

<button id="send_response_button">Antworten</button>
<button id="add_response_button">Kundenantwort einfügen</button>

<br />
<?php foreach ($entries as $entry): ?>
<fieldset>
	<legend>
		<?php echo ($entry['is_response']) ? "USER XYZ" : "$ticket[inquirer_first_name] $ticket[inquirer_last_name]"; ?> | <?php echo date('d.m.Y G:i', $entry['timestamp_created']) . ' Uhr'; ?>
	</legend>
	<textarea class="autoresize" style="width: 100%;"><?php echo $entry['text']; ?></textarea>
</fieldset>
<?php endforeach; ?>

<div id="send_response_dialog" style="font-size: 10pt">
	<form id="send_response_form" action="<?php echo $ot->get_link('ticket', $ticket['id']); ?>" method="POST" enctype="multipart/form-data">
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