<?php
$status_codes = array(0 => 'Offen', 1 => 'Beantwortet', 2 => 'Geschlossen');

$show = (isset($_GET['show'])) ? $_GET['show'] : 50;
$page = (isset($_GET['show'])) ? $_GET['show'] : 0;
$start = $page * $show;

$query = "	SELECT t.id, c.name AS category, t.status, t.timestamp_created,
				MAX(e.timestamp_created) AS last_response, COUNT(e.id) AS entry_count
			FROM ot_ticket AS t
			JOIN ot_ticket_entry AS e
				ON e.ticket_id = t.id
			LEFT JOIN ot_ticket_category AS c
				ON c.id = t.ticket_category_id
			GROUP BY t.id
			ORDER BY t.status, last_response DESC
			LIMIT $start, $show";

$result = MySQL::query($query);

$data = array();
while ($row = MySQL::fetch($result)) {
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
		<a href="<?php echo $ot->get_link('ticket', 0, '', array('page' => $page+1, 'show' => $show)) ; ?>">Nächste Seite</a>
	<?php endif; ?>

<?php else: ?>
	<p>Es wurden keine Tickets gefunden.</p>

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