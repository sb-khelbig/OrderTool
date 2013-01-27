<?php include 'ot_import_action_data.php'; ?>

<h1><?php echo $import['file_name']; ?></h1>

<form action="index.php?p=import" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="has_header" value="<?php echo $has_header; ?>" />
	<input type="hidden" name="action" value="store" />
	<input type="hidden" name="id" value="<?php echo $import['id']; ?>" />
	
	<div id="settings">
	<h2>Einstellungen</h2>
		<table>
			<tr>
				<td>Name der Liste</td>
				<td><input type="text" name="list_name" placeholder="<?php echo $import['file_name']; ?>" /></td>
			</tr>
		</table>
	</div>
	
	<div id="header">
		<h2>Daten</h2>
		<?php include 'ot_import_action_table.php'; ?>
	</div>
	
	<br />
	<input type="submit" value="Datei speichern" />
</form>