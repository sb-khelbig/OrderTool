<?php
$import = get_row_by_id($_GET['id'], 'ot_import') or die("ID existiert nicht!");

if ($import['timestamp_stored']) {
	$result = mysql_query("	SELECT  r.id, r.error, c.pos, c.data
							FROM ot_import_row AS r, ot_import_column AS c
							WHERE r.id = c.import_row_id
								AND r.import_id = $import[id]
								AND r.error < 0") or die('MYSQLError: ' . mysql_error());
} else {
	$result = mysql_query("	SELECT  r.id, c.pos, c.data
							FROM ot_import_row AS r, ot_import_column AS c
							WHERE r.id = c.import_row_id
								AND r.import_id = $import[id]") or die('MYSQLError: ' . mysql_error());
}
$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	if (array_key_exists($row['id'], $rows)) {
		$rows[$row['id']][$row['pos']] = $row['data'];
	} else {
		$rows[$row['id']] = array($row['pos'] => $row['data']);
	}
}?>
	
<h1><?php echo $import['file_name']; ?></h1>

<?php if ($rows): ?>
	<form action="<?php echo "index.php?p=import&id=$import[id]"; ?>" method="POST" enctype="multipart/form-data">
		<div>
			<label for="has_header">Datei hat Spaltenbezeichnungen</label>
			<input type="checkbox" name="has_header" checked /> <br />
			<select name="action">
				<option value="newlist">Zu neuer Liste hinzufügen</option>
				<option value="existinglist">Zu bestehender Liste hinzufügen</option>
				<option value="statusupdate">Status aktualisieren</option>
			</select>
			<input id="submit" type="submit" value="Weiter" />
		</div>
	</form>
		
	<table>
		<tbody>
			<?php foreach ($rows as $id => $row): ?>
			<tr id="<?php echo $id; ?>">
				<?php foreach ($row as $pos => $column): ?>
				<td><?php echo $column; ?></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
<?php else: ?>
	<?php if ($import['timestamp_stored']): ?>
		<p>Datei fehlerfrei gespeichert.</p>
		
	<?php else: ?>
		<p>Datei enthält keine Daten.</p>
		
	<?php endif; ?>
	
<?php endif; ?>