<?php
$status_codes = array(0 => 'Offen', 1 => 'Beantwortet', 2 => 'Geschlossen');
$show = (isset($_GET['show'])) ? $_GET['show'] : 50;
$page = (isset($_GET['show'])) ? $_GET['show'] : 0;
$start = $page * $show;
$result = mysql_query("	SELECT t.id, c.name AS category, t.status, t.timestamp_created,
							MAX(e.timestamp_created) AS last_response, COUNT(e.id) AS entry_count
						FROM ot_ticket AS t
						JOIN ot_ticket_entry AS e
							ON e.ticket_id = t.id
						LEFT JOIN ot_ticket_category AS c
							ON c.id = t.ticket_category_id
						GROUP BY t.id
						ORDER BY t.status, last_response DESC
						LIMIT $start, $show") or die('MySQLError: ' . mysql_error());
$data = array();
while ($row = mysql_fetch_assoc($result)) {
	$data[] = $row;
} ?>

<h1 id="headline">Tickets</h1>

<?php if ($data): ?>
	<div class="overview">
		<form>
			<div class="actions">
				<label>
					Aktion:
					<select name="action">
						<option value="0">Erledigt setzen</option>
					</select>
				</label>
				<input type="submit" value="Anwenden" />
			</div>
			<table class="table">
				<thead>
					<tr>
						<th class="action-select"><input id="action-toggle" type="checkbox" /></th>
						<th>ID</th>
						<th>Kategorie</th>
						<th>Status</th>
						<th>Erstellt</th>
						<th>Letzte Bearbeitung</th>
						<th>Einträge</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($data as $i => $row): ?>
					<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'; ?>">
						<td class="action-select"><input class="action-selectbox" type="checkbox" name="ids[]" value="<?php echo $row['id']; ?>" /></td>
						<td><a href="<?php echo $ot->get_link('ticket', $row['id']); ?>"><?php echo $row['id']; ?></a></td>
						<td><?php echo $row['category']; ?></td>
						<td><?php echo $status_codes[$row['status']]; ?></td>
						<td><?php echo date('d.m.Y G:i', $row['timestamp_created']); ?></td>
						<td><?php echo date('d.m.Y G:i', $row['last_response']); ?></td>
						<td><?php echo $row['entry_count']; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	</div>
	
	<?php if (count($data) > $show): ?>
		<a href="<?php echo 'index.php?p=ticket&page=' . ($page+1); ?>">Nächste Seite</a>
	<?php endif; ?>

<?php else: ?>
	<p>Es wurden keine Tickets gefunden.</p>

<?php endif; ?>

<button id="create_ticket_button">Ticket erstellen</button>

<div id="create_ticket_dialog" style="font-size: 10pt">
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
			<?php echo create_dropdown_menu('ticket_category_id', 'ot_ticket_category', 'Unbekannt'); ?> <br />
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
				<option value="ot_row">Bestellung</option>
				<option value="ot_order_list">Produkt</option>
			</select>
			<input type="number" name="ref_id" />
		</fieldset>
		<fieldset>
			<legend>Text</legend>
			<textarea rows="20" cols="80"></textarea>
		</fieldset>
	</form>
</div>
	
<script>
	$(document).ready(function () {
		$('#action-toggle').bind('click', function () {
			$checked = $(this).prop('checked');
			$('.action-selectbox', '.overview .table').prop('checked', $checked);
		});
	
		$('#create_ticket_button').bind('click', function () {
			$('#create_ticket_dialog').dialog('open');
		});
	
		$('#create_ticket_dialog').dialog({
			title: 'Ticket erstellen',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Erstellen': function () {
					$('#create_ticket_form').submit();
				}
			}
		});
			      
	});
</script>