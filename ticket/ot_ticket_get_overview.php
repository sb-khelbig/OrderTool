<?php $status_codes = array(0 => 'Offen', 1 => 'In Bearbeitung', 2 => 'Beantwortet', 3 => 'Geschlossen');

$tickets = Ticket::all();

$index = array();
foreach ($tickets as $id => $ticket) {
	$index[$id] = $ticket->last_edited();
}
asort($index);

// Caching
TicketCategory::all(); ?>

<h1>Tickets</h1>

<?php if ($tickets): ?>
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
				<?php foreach (array_reverse($index, TRUE) as $id => $last_edited): ?>
					<tr>
						<td class="action-select"><input class="action-selectbox" type="checkbox" name="ids[]" value="<?php echo $id; ?>" /></td>
						<td><a href="<?php echo $ot->get_link('ticket', $id); ?>"><?php echo $id; ?></a></td>
						<td><?php echo ($cat = $tickets[$id]->category) ? $cat->name : 'Unbekannt'; ?></td>
						<td><?php echo $status_codes[$tickets[$id]->status]; ?></td>
						<td><?php echo date('d.m.Y G:i', $tickets[$id]->created); ?></td>
						<td><?php echo date('d.m.Y G:i', $last_edited); ?></td>
						<td><?php echo $tickets[$id]->entry_count(); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	</div>

<?php else: ?>
	<p>Keine Tickets vorhanden!</p>

<?php endif; ?>

<button class="create_ticket_button" id="create_ticket_button">Ticket erstellen</button>

<?php
$create_ticket_data = array(
		'dialog_id' => 'create_ticket_dialog',
	);
include 'ticket/ot_ticket_dialog_functions.php';
include 'ticket/ot_ticket_dialog.php'; 
include 'ticket/ot_ticket_dialog_js.php'; ?>
	
<script>
	$(document).ready(function () {
		$('#action-toggle').bind('click', function () {
			$checked = $(this).prop('checked');
			$('.action-selectbox', '.overview .table').prop('checked', $checked);
		});
	
		register_ticket_dialog($('#create_ticket_button'));
			      
	});
</script>