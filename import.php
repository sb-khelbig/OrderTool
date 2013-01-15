<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			
			case 'upload':
				if ($_FILES['file']['error'] == 0) {
					$timestamp = time();
					$file_name = $timestamp . '_' . $_FILES['file']['name'];
					$file_has_header = isset($_POST['has_header']);
					if (move_uploaded_file($_FILES['file']['tmp_name'], "files/$file_name")) {
						$result = mysql_query("INSERT INTO ot_import (user_id, file, file_has_header, timestamp_created) VALUES ($_SESSION[UserID], '$file_name', $file_has_header, $timestamp)");
						$import_id = mysql_insert_id();
						if ($import_id) {
							log_action(1, $import_id);
							header("Location: index.php?p=import&id=$import_id");
						} else {
							unlink("files/$file_name");
							echo "Datenbankfehler!";
						}
					} else {
						echo "Datei konnte nicht verschoben werden!";
					}
				} else {
					echo 'Dateifehler, Code: ' . $_FILES['file']['error'];
				}
				break;
				
			case 'store':
				if ($import = get_row_by_id($_POST['id'], 'ot_import')) {
					
					// has_header setzen und gegebenenfalls in der Datenbank aktualisieren
					$has_header = (isset($_POST['has_header'])) ? 1: 0;
					if ($has_header != $import['file_has_header']) {
						$result = mysql_query("UPDATE ot_import SET file_has_header=$has_header WHERE id=$import[id]");
					}
					
					// Datei öffnen
					if ($f = fopen("files/$import[file]", 'r')) {
						
						// Daten zu bestehender Liste hinzufügen
						if ($_POST['list']) {
							if ($order_list = get_row_by_id($_POST['list'], 'ot_order_list')) {
								echo "TODO";
							} else {
								echo "Die liste mit der ID $_POST[list] existiert nicht!";
							}
						} 
						
						// neue Liste anlegen
						else {
							$result = mysql_query("INSERT INTO ot_order_list (name) VALUES ('Unbenannte Liste ($import[file])')");
							if (mysql_insert_id()) {
								$order_list = array('id' => mysql_insert_id());
								log_action(2, $order_list['id']);
								
								// Header auslesen
								$headers = array();
								foreach (preg_grep('/^header_[0-9]+$/', array_keys($_POST)) as $key) {
									$headers[trim($key, 'header_')] = mysql_real_escape_string($_POST[$key]);
								}
								
								// Header anlegen
								$insert = array();
								if ($has_header) {
									$row = fgetcsv($f, 0, ';');
									foreach ($headers as $pos => $header_id) {
										$insert[] = "($order_list[id], $header_id, $pos, '$row[$pos]')";
									}
								} else {
									foreach ($headers as $pos => $header_id) {
										$insert[] = "($order_list[id], $header_id, $pos, '')";
									}
								}
								
								$values = join(', ', $insert);
								$result = mysql_query("INSERT INTO ot_order_list_has_header (order_list_id, header_id, pos, original_label) VALUES $values");
								log_action(5, $order_list['id']);
								
								// Rows anlegen
								$rows = array(); $columns = array();
								
								$timestamp = time();
								while ($row = fgetcsv($f, 0, ';')) {
									$rows[] = "($order_list[id], $import[id], $timestamp)";
									$columns[] = $row;
								}
								
								$values = join(', ', $rows);
								$result = mysql_query("INSERT INTO ot_row (order_list_id, import_id, timestamp_created) VALUES $values");
								$result = mysql_query("SELECT id FROM ot_row WHERE timestamp_created=$timestamp");
									
								// Columns speichern
								$i = 0;
								$query = array();
								while ($id = mysql_fetch_assoc($result)) {
									$pos = 0;
									foreach ($columns[$i++] as $column) {
										$tmp = trim($column);
										$query[] = "($id[id], " . $pos++ . ", '$tmp')";
									}
								}
								
								$values = join(', ', $query);
								$result = mysql_query("INSERT INTO ot_column (row_id, pos, data) VALUES $values");
								
								$result = mysql_query("UPDATE ot_import SET stored=1 WHERE id=$import[id]");
								
								log_action(6, $import[id]);
								log_action(4, $order_list['id']);
								
								header("Location: index.php?p=orderlists&id=$order_list[id]");
								
							} else {
								echo "Datenbankfehler!";
							}
						}
						fclose($f);
					} else {
						echo 'Fehler beim öffnen der Datei!';
					}
				}
				break;
				
			default:
				echo 'Unsupported action!';
				break;
		}
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php if ($import = get_row_by_id($_GET['id'], 'ot_import')): ?>
			<?php if ($f = fopen("files/$import[file]", 'r')): ?>
			<h1>Import ID <?php echo $import['id']; ?></h1>
			<div>
				<h3>Allgemeine Informationen</h3>
				<table>
					<tr>
						<td>Datei:</td>
						<td><?php echo $import['file']; ?></td>
					</tr>
					<tr>
						<td>Datum:</td>
						<td><?php echo date('d.m.Y G:i', $import['timestamp_created']); ?></td>
					</tr>
				</table>
			</div>
			<form action="index.php?p=import" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="action" value="store" />
				<input type="hidden" name="id" value="<?php echo $import['id']; ?>" />
				<div>
					<h3>Einstellungen</h3>
					<table>
						<tr>
							<td>Spaltenbezeichnungen</td>
							<td><input type="checkbox" value="1" name="has_header" <?php if ($import['file_has_header']) echo 'checked'; ?> /></td>
						</tr>
						<tr>
							<td>Liste</td>
							<td><?php echo create_dropdown_menu('list', 'ot_order_list', 'Neue Liste erstellen'); ?></td>
						</tr>
						<tr>
							<td colspan=2><input type="submit" value="Datei speichern" /></td>
						</tr>
					</table>
				</div>
				<div>
					<h3>Dateiinhalt</h3>
					<table>
						<?php $row = fgetcsv($f, 0, ';'); $pos = 0; ?>
						<tr>
						<?php foreach ($row as $column): ?>
							<?php $optional = ($import['file_has_header']) ? "$column *" : 'Optional'; ?>
							<td><?php echo create_dropdown_menu('header_' . $pos++, 'ot_header', $optional); ?></td>
						<?php endforeach; ?>
						</tr>
						<?php if (!$import['file_has_header']): ?>
						<tr>
						<?php foreach ($row as $column): ?>
							<td><?php echo $column; ?></td>
						<?php endforeach; ?>
						</tr>
						<?php endif; ?>
						<?php while ($row = fgetcsv($f, 0, ';')): ?>
						<tr>
							<?php foreach ($row as $column): ?>
							<td><?php echo $column; ?></td>
							<?php endforeach; ?>
						</tr>
						<?php endwhile; ?>
					</table>
				</div>
			</form>
			<?php fclose($f); ?>
			<?php else: ?>
			<p>Datei konnte nicht gefunden werden.</p>
			<?php endif; ?>
		<?php else: ?>
		<p>ID existiert nicht!</p>
		<?php endif; ?>
		
	
	<?php else: ?>
	<h1>Imports</h1>
	
	<?php $imports = mysql_query("SELECT * FROM ot_import"); ?>
	<div>
		<table>
			<tr>
				<td>ID</td>
				<td>Datei</td>
				<td>User</td>
				<td>Zeit</td>
				<td>Erledigt</td>
			</tr>
			<?php while ($import = mysql_fetch_assoc($imports)): ?>
			<tr>
				<td><a href="<?php echo "?p=import&id=$import[id]"?>" ><?php echo $import['id']; ?></a></td>
				<td><?php echo $import['file']; ?></td>
				<td><?php echo $import['user_id']; ?></td>
				<td><?php echo date('d.m.Y G:i', $import['timestamp_created']); ?></td>
				<td><input type="checkbox" <?php if ($import['stored']) echo 'checked'; ?> /></td>
			</tr>
			<?php endwhile; ?>
		</table>
	</div>
	<br />
	<div>
		<form action="index.php?p=import" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="action" value="upload" />
			Datei: <input type="file" name="file" /> <br />
			Datei hat Spaltenbezeichnungen: <input type="checkbox" name="has_header" value="1" checked />  <br />
			<input type="submit" value="Hochladen" />
		</form>
	</div>
	
	<?php endif; ?>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>