<?php 
$show = (isset($_GET['show'])) ? $_GET['show'] : 50;
$page = (isset($_GET['show'])) ? $_GET['show'] : 0;
$start = $page * $show;
$result = mysql_query("	SELECT 	i.id, i.user_id, i.file_name, i.timestamp_created,
								i.timestamp_stored, i.file_row_count,
								SUM(CASE WHEN r.error > 1 THEN 1 ELSE 0 END) AS error_count
						FROM ot_import AS i
						LEFT JOIN ot_import_row AS r
							ON i.id = r.import_id
						GROUP BY i.id
						ORDER BY i.timestamp_created DESC
						LIMIT $start, $show");
if ($result) {
	$imports = array();
	while ($import = mysql_fetch_assoc($result)) {
		$imports[] = $import;
	}
} else {
	die(mysql_error());
} ?>

<h1 id="headline">Imports</h1>

<?php if ($imports): ?>
	<div class="overview">
		<form>
			<div class="actions">
				<label>
					Aktion:
					<select name="action">
						<option value="0">Erledigt setzen</option>
					</select>
				</label>
			</div>
			<table class="table">
				<thead>
					<tr>
						<th class="action-select"><input id="action-toggle" type="checkbox" /></th>
						<th>Datei</th>
						<th>User</th>
						<th>Zeit</th>
						<th>Erledigt</th>
						<th>Zeilen</th>
						<th>Erfolgreich</th>
						<th>Fehler</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($imports as $i => $import): ?>
					<tr class="<?php echo ($i%2 == 0) ? 'even' : 'odd'; ?>">
						<td class="action-select"><input class="action-selectbox" type="checkbox" name="_ids" value="<?php echo $import['id']; ?>" /></td>
						<td><a href="<?php echo "index.php?p=import&id=$import[id]"?>"><?php echo $import['file_name']; ?></a></td>
						<td><?php echo $import['user_id']; ?></td>
						<td><?php echo date('d.m.Y G:i', $import['timestamp_created']); ?></td>
						<td><input type="checkbox" <?php echo ($import['timestamp_stored']) ? 'checked': ''; ?> /></td>
						<td><?php echo $import['file_row_count']; ?></td>
						<td><?php echo $import['file_row_count'] - $import['error_count']; ?></td>
						<td><?php echo $import['error_count']; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	</div>
	<div>
		<button id="upload_file_button">Neue Datei hochladen</button>
	</div>
	
	<?php if (count($imports) > $show): ?>
		<a href="<?php echo 'index.php?p=import&page=' . ($page+1); ?>">Nächste Seite</a>
	<?php endif; ?>
	
	<div id="upload_file_dialog">
		<form id="upload_file_form" action="index.php?p=import" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="action" value="upload" />
			<input type="file" name="file" />
		</form>
	</div>
	
	<script>
	$(document).ready(function () {
		$('#action-toggle').bind('click', function () {
			$checked = $(this).prop('checked');
			$('.action-selectbox', '.overview .table').prop('checked', $checked);
		});

		$('#upload_file_button').bind('click', function () {
			$('#upload_file_dialog').dialog('open');
		});

		$('#upload_file_dialog').dialog({
			title: 'Neue Datei hochladen',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Datei hochladen': function () {
					$('#upload_file_form').submit();
				}
			}
		});
			      
	});
	</script>

<?php else: ?>
	<p>Es wurden keine Imports gefunden.</p>

<?php endif; ?>

