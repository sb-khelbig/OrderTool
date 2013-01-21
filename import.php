<?php //ini_set('auto_detect_line_endings',true); ?>
<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			
			case 'upload':
				if ($_FILES['file']['error'] == 0) {
					$timestamp = time();
					$file_name = $timestamp . '_' . $_FILES['file']['name'];
					$opt_has_header = (isset($_POST['has_header'])) ? 1 : 0;
					if (move_uploaded_file($_FILES['file']['tmp_name'], "files/$file_name")) {
						$result = mysql_query("INSERT INTO ot_import (file, opt_has_header, timestamp_created) VALUES ('$file_name', $opt_has_header, $timestamp)");
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
					
					// Variablen überprüfen und Auswahl in der DB speichern
					$has_header = (isset($_POST['has_header'])) ? 1 : 0;
					$list = (isset($_POST['list'])) ? $_POST['list'] : 0;
					$status = (isset($_POST['status'])) ? get_row_by_id($_POST['status'], 'ot_order_status') : 0;
					$status = ($status) ? $status['id'] : 0;
					$matching = (isset($_POST['matching'])) ? get_row_by_id($_POST['matching'], 'ot_header') : 0;
					$matching_id = ($matching) ? $matching['id'] : 0;
					
					$result = mysql_query("UPDATE ot_import SET opt_has_header=$has_header, opt_order_list=$list, opt_order_status=$status, opt_matching=$matching_id WHERE id=$import[id]") or die('Optionen updaten' + mysql_error());
					
					// Datei öffnen
					if ($f = fopen("files/$import[file]", 'r')) {
						
						// Daten zu bestehender Liste hinzufügen
						if ($list) {
							if ($order_list = get_row_by_id($list, 'ot_order_list')) {
								
								// Kein Matching, Daten hinzufügen
								if (!$matching) {
									$rows = array(); $columns = array(); $timestamp = time();
									$headers = mysql_query("SELECT * FROM ot_order_list_has_header WHERE order_list_id=$order_list[id]");
									
									$row = fgetcsv($f, 0, ';');
									
									// Spaltenzahl muss übereinstimmen
									if (count($row) == mysql_num_rows($headers)) {
										
										// Spaltenbezeichnungen nicht speichern
										if (!$has_header) {
											$rows[] = "($order_list[id], $import[id], $timestamp)";
											$columns[] = $row;
										}
										
										// Rows speichern
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
										
										log_action(6, $import[id]); log_action(4, $order_list['id']);
										
										header("Location: index.php?p=orderlists&id=$order_list[id]");
									}
									
									// Spaltenzahl stimmt nicht überein
									else {
										echo "Spaltenzahl stimmt nicht überein!";
									}
								}
								
								// Matching, Daten aktualisieren
								else {
									// Header der Liste abfragen
									$headers = array();
									$result = mysql_query("SELECT * FROM ot_order_list_has_header WHERE order_list_id=$order_list[id]");
									$optional = 1;
									while ($header = mysql_fetch_assoc($result)) {
										$id = ($header['header_id']) ? $header['header_id'] : 'Optional ' . $optional++;
										$headers[$id] = $header;
									}
									
									print_r($headers); echo '<br>';
									
									// Header des Uploads aus der Form auslesen
									$headers_selected = array();
									foreach (preg_grep('/^header_[0-9]+$/', array_keys($_POST)) as $key) {
										$headers_selected[trim($key, 'header_')] = mysql_real_escape_string($_POST[$key]);
									}
									
									print_r($headers_selected); echo '<br>';
									
									$matching_row = array_search($matching['id'], $headers_selected); // Position der Spalte, die die UID enthält
									$matching_pos = 0;
									foreach ($headers as $id => $h) {
										if ($id == $matching['id']) {
											$matching_pos = $h['pos'];
											break;
										}
									}
									$toUpdate = array();
									$toInsert = array();
									foreach ($headers_selected as $index => $id) {
										echo "$index, $id <br>";
										// Matchingspalte überspringen
										if ($index == $matching_row) continue;
										// vorhandene Spalte aktualisieren
										if (isset($headers[$id])) {
											$toUpdate[$index] = $headers[$id]['pos'];
										}
										// definierte Spalte hinzufügen
										else {
											$toInsert[$index] = $id;
										}
									}
									
									print_r($toUpdate); echo '<br>';
									print_r($toInsert); echo '<br>';
									
									$file_row = -1;
									
									// Spaltenbezeichnungen auslassen
									if ($has_header) {
										$row = fgetcsv($f, 0, ';');
										$file_row++;
									}
									
									mysql_query("START TRANSACTION");
									try {
										// neue Header anlegen
										if ($toInsert) {
											$column_count = count($headers);
											$new_headers = array();
											foreach ($toInsert as $index => $id) {
												$label = ($has_header) ? $row[$index] : '';
												$pos = $column_count++;
												$new_headers[] = "($order_list[id], $id, $pos, '$label')";
												$toInsert[$index] = $pos;
											}
											
											$values = join(', ', $new_headers);
											print_r($values); echo '<br>';
											$result = mysql_query_with_error("INSERT INTO ot_order_list_has_header (order_list_id, header_id, pos, original_label) VALUES $values");
										}
										
										$errors = array();
										while ($row = fgetcsv($f, 0, ';')) {
											$file_row++;
											$match = trim($row[$matching_row]);
											
											$result = mysql_query("SELECT ot_row.id FROM ot_row, ot_column WHERE ot_column.row_id = ot_row.id AND order_list_id=$order_list[id] AND ot_column.data = '$match' AND ot_column.pos='$matching_pos'");
											if ($num_rows = mysql_num_rows($result)) {
												if ($num_rows == 1) {
													$row_id = mysql_fetch_assoc($result);
													$row_id = $row_id['id'];
													
													$update = array();
													foreach ($toUpdate as $index => $pos) {
														$data = trim($row[$index]);
														$update[] = "pos=$pos THEN '$data'";
													}
													
													$insert = array();
													foreach ($toInsert as $index => $pos) {
														$data = trim($row[$index]);
														$insert[] = "($row_id, $pos, '$data')";
													}
													
													if ($update) {
														$cases = join(' WHEN ', $update);
														$positions = join(', ', $toUpdate);
														$result = mysql_query_with_error("UPDATE ot_column SET data = CASE WHEN $cases END WHERE pos IN ($positions) AND row_id=$row_id");
													}
													
													if ($insert) {
														$values = join(', ', $insert);
														$result = mysql_query_with_error("INSERT INTO ot_column (row_id, pos, data) VALUES $values");
													}
													
												} else {
													$errors[] = "($import[id], $file_row, 'Identifier not unique')";
													continue;
												}
											} else {
												$errors[] = "($import[id], $file_row, 'Identifier not found')";
												continue;
											}
										}
										
										if ($errors) {
											$values = join(', ', $errors);
											$result = mysql_query_with_error("INSERT INTO ot_import_error (import_id, row, message) VALUES $values");
										}
										
										// fehlende Spalten auffüllen
										$result = mysql_query("SELECT row_id as id, COUNT(*) as column_count FROM ot_column WHERE row_id IN (SELECT id FROM ot_row WHERE order_list_id=$order_list[id]) GROUP BY row_id");
										$insert = array();
										while ($row = mysql_fetch_assoc($result)) {
											$pos = $row['column_count'];
											while ($pos < $column_count) {
												$insert[] = "($row[id], $pos)";
												$pos++;
											}
										}
										
										if ($insert) {
											$values = join(', ', $insert);
											$result = mysql_query_with_error("INSERT INTO ot_column (row_id, pos) VALUES $values");
										}
										
										mysql_query("COMMIT"); // commit!
										
									} catch (Exception $e) {
										print_r($e);
										mysql_query("ROLLBACK");
									}
								}
							} 
							
							// gewählte Liste existiert nicht
							else {
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

								// Columns & Status speichern
								$i = 0;
								$query_s = array(); // Status
								$query_c = array(); // Columns
								while ($id = mysql_fetch_assoc($result)) {
									$pos = 0;
									$query_s[] = "($id, $status, $timestamp)";
									foreach ($columns[$i++] as $column) {
										$tmp = trim($column);
										$query_c[] = "($id[id], " . $pos++ . ", '$tmp')";
									}
								}
								
								// Status
								if (!$status) {
									$values = join(', ', $query_s);
									$result = mysql_query("INSERT INTO ot_row_has_order_status (row_id, order_status_id, timestamp_created) VALUES $values");
								}
								
								// Columns
								$values = join(', ', $query_c);
								$result = mysql_query("INSERT INTO ot_column (row_id, pos, data) VALUES $values");
								
								$result = mysql_query("UPDATE ot_import SET stored=1 WHERE id=$import[id]");
								
								log_action(6, $import[id]); log_action(4, $order_list['id']);
								
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
					<?php if ($import['stored']): ?>
					<tr>
						<td>Gespeichert:</td>
						<td><input type="checkbox" checked /></td>
					</tr>
					<tr>
						<td>Fehler:</td>
						<td><?php $result = mysql_query("SELECT * FROM ot_import_error WHERE import_id=$import[id]"); echo mysql_num_rows($result); ?></td>
					</tr>
					<?php endif; ?>
				</table>
			</div>
			<?php $errors = array();
			while ($row = mysql_fetch_assoc($result)) {
				$errors[$row['row']] = $row;
			}
			?>
			<form action="index.php?p=import" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="action" value="store" />
				<input type="hidden" name="id" value="<?php echo $import['id']; ?>" />
				<div>
					<h3>Einstellungen</h3>
					<table id="settings_table">
						<tr>
							<td>Spaltenbezeichnungen</td>
							<td><input type="checkbox" value="1" name="has_header" <?php if ($import['opt_has_header']) echo 'checked'; ?> /></td>
						</tr>
						<tr>
							<td>Liste</td>
							<td><?php echo create_dropdown_menu('list', 'ot_order_list', 'Neue Liste erstellen'); ?></td>
						</tr>
						<tr>
							<td>Status</td>
							<td><?php echo create_dropdown_menu('order_status', 'ot_order_status', 'Status beibehalten'); ?></td>
						</tr>
						<tr>
							<td>Aktualisierung</td>
							<td>
								<select name="matching" disabled="disabled">
									<option value="0">Kein Abgleich</option>
								</select>
						</tr>
						<tr>
							<td colspan=2><input type="submit" value="Datei speichern" /></td>
						</tr>
					</table>
				</div>
				<div>
					<h3>Dateiinhalt</h3>
					<table>
						<?php $row = fgetcsv($f, 0, ';'); $pos = 0; $file_row = 0; ?>
						<tr>
						<?php foreach ($row as $column): ?>
							<?php $optional = ($import['opt_has_header']) ? "$column *" : 'Optional'; ?>
							<td><?php echo create_dropdown_menu('header_' . $pos++, 'ot_header', $optional); ?></td>
						<?php endforeach; ?>
						</tr>
						<?php if (!$import['opt_has_header']): ?>
						<tr <?php if (isset($errors[$file_row])) echo 'class="error"'; ?>>
						<?php foreach ($row as $column): ?>
							<td><?php echo $column; ?></td>
						<?php endforeach; ?>
						</tr>
						<?php endif; ?>
						<?php while ($row = fgetcsv($f, 0, ';')): ?>
						<tr <?php if (isset($errors[++$file_row])) echo 'class="error"'; ?>>
							<?php foreach ($row as $column): ?>
							<td><?php echo $column; ?></td>
							<?php endforeach; ?>
						</tr>
						<?php endwhile; ?>
					</table>
				</div>
			</form>
			<script type="text/javascript">
				function add_options(options) {
					var select = $("select[name='matching']");
					$.each(options, function (key, value) {
						var opt = new Array('<option value="', key, '">', value, '</option>');
						$(opt.join('')).appendTo(select);
					});
				};
				
				function load_matching_options(elem) {
					var id = $(this).children(':selected').attr('value');
					var select = $("select[name='matching']");
					if (id != '0') {
						select.children('*:not([value="0"])').remove();
						select.removeAttr('disabled');
						jQuery.get('ajax/ajax_import.php',
								{id: id},
								add_options,
								'json');
					}
					if (id == '0') {
						select.attr('disabled', 'disabled');
					}
				};
				
				$(document).ready(function () {
					$("select[name='list']").bind('change', load_matching_options);
				});
			</script>
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
				<td><a href="<?php echo "?p=import&id=$import[id]"?>" ><?php echo $import['file']; ?></a></td>
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