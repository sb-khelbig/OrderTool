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
				
			case 'errors':
				if ($import = get_row_by_id($_POST['id'], 'ot_import')) {
					if ($f = fopen("files/$import[file]", 'r')) {
						
						// Datei Laden
						$rows = array();
						while ($row = fgetcsv($f, 0, ';')) {
							$rows[] = $row;
						}
						fclose($f);
						
						$headers_selected = array();
						foreach (preg_grep('/^header_[0-9]+$/', array_keys($_POST)) as $key) {
							$headers_selected[trim($key, 'header_')] = mysql_real_escape_string($_POST[$key]);
						}
						
						$errors_selected = array();
						foreach (preg_grep('/^error_[0-9]+$/', array_keys($_POST)) as $key) {
							$errors_selected[trim($key, 'error_')] = mysql_real_escape_string($_POST[$key]);
						}
						
						$values = join(', ', array_keys($errors_selected));
						$result = mysql_query("	SELECT id, row
												FROM ot_import_error
												WHERE id IN ($values)
													AND import_id = $import[id]");
						$inserts = array();
						$updates = array();
						$deletes = array();
						while ($error = mysql_fetch_assoc($result)) {
							switch ($errors_selected[$error['id']]) {
								case '1':
									$deletes[] = $error['id'];
								case '2':
									$row = $rows[$error['row']];
									// TODO: Update oder Insert
									break;
								default:
									break;
							}
						}
						if ($deletes) {
							$values = join(', ', $deletes);
							$result = mysql_query("DELETE FROM ot_import_error WHERE id IN ($values)");
						}
						
					} else {
						echo "Datei konnte nicht geöffnet werden!";
					}
				}
				break;
				
			case 'store':
				if ($import = get_row_by_id($_POST['id'], 'ot_import')) {
					
					// Variablen überprüfen und Auswahl in der DB speichern
					$has_header = (isset($_POST['has_header'])) ? 1 : 0;
					$list = (isset($_POST['list'])) ? $_POST['list'] : 0;
					$status = (isset($_POST['status'])) ? get_row_by_id_as_array($_POST['status'], 'ot_order_status') : array('id' => 0);
					$matching = (isset($_POST['matching'])) ? get_row_by_id_as_array($_POST['matching'], 'ot_header') : array('id' => 0);
					
					$result = mysql_query("UPDATE ot_import SET opt_has_header=$has_header, opt_order_list=$list, opt_order_status=$status[id], opt_matching=$matching[id] WHERE id=$import[id]") or die('Optionen updaten' + mysql_error());
					
					// Datei öffnen
					if ($f = fopen("files/$import[file]", 'r')) {
						
						// Daten zu bestehender Liste hinzufügen
						if ($list) {
							if ($order_list = get_row_by_id($list, 'ot_order_list')) {
								
								// Kein Matching, Daten hinzufügen
								if (!$matching['id']) {
									$rows = array(); $columns = array(); $timestamp = time();
									$headers = mysql_query("SELECT * FROM ot_order_list_has_header WHERE order_list_id=$order_list[id]");
									
									$row = fgetcsv($f, 0, ';'); $file_row = 0;
									
									// Spaltenzahl muss übereinstimmen
									if (count($row) == mysql_num_rows($headers)) {
										
										// Spaltenbezeichnungen nicht speichern
										if (!$has_header) {
											$rows[] = "($order_list[id], $import[id], $timestamp)";
											$columns[] = $row;
										}
										
										// Rows speichern
										while ($row = fgetcsv($f, 0, ';')) {
											$file_row++;
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
										
										// Import updaten
										$file_row++;
										$result = mysql_query("UPDATE ot_import SET file_row_count=$file_row, stored=1 WHERE id=$import[id]");
										
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
									$result = mysql_query("	SELECT *
															FROM ot_order_list_has_header
															WHERE order_list_id = $order_list[id]");
									$optional = 1;
									$headers = array();
									while ($header = mysql_fetch_assoc($result)) {
										$id = ($header['header_id']) ? $header['header_id'] : 'Optional ' . $optional++;
										$headers[$id] = $header;
									}
									
									// Matching
									if (array_key_exists($matching['id'], $headers)) {
										$matching_pos = $headers[$matching['id']]['pos'];
									} else {
										die('Matching-Header existiert nicht');
									}
									
									$result = mysql_query("	SELECT c.data AS data, r.id AS id
															FROM ot_column AS c, ot_row AS r
															WHERE r.id = c.row_id
																AND r.order_list_id = $order_list[id]
																AND c.pos = $matching_pos");
									$toMatch = array();
									while ($row = mysql_fetch_assoc($result)) {
										$toMatch[$row['data']] = $row['id']; 
									}
									
									// Columns
									$result = mysql_query("	SELECT r.id AS row_id, c.id AS col_id, c.pos AS pos
															FROM ot_column AS c, ot_row AS r
															WHERE r.id = c.row_id
																AND r.order_list_id = $order_list[id]
																AND c.pos != $matching_pos");
									$columns = array();
									while ($row = mysql_fetch_assoc($result)) {
										if (array_key_exists($row['row_id'], $columns)) {
											$columns[$row['row_id']][$row['pos']] = $row['col_id'];
										} else {
											$columns[$row['row_id']] = array($row['pos'] => $row['col_id']);
										}
									}
									
									// Header des Uploads aus der Form auslesen
									$headers_selected = array();
									foreach (preg_grep('/^header_[0-9]+$/', array_keys($_POST)) as $key) {
										$headers_selected[trim($key, 'header_')] = mysql_real_escape_string($_POST[$key]);
									}
									
									$matching_row = array_search($matching['id'], $headers_selected); // Position der Spalte, die die UID enthält
									
									// Spaltenpositionen
									$toUpdate = array();
									$toInsert = array();
									foreach ($headers_selected as $index => $id) {
										if ($index == $matching_row) {
											continue;
										} elseif (array_key_exists($id, $headers)) {
											$toUpdate[$index] = $headers[$id]['pos'];
										} else {
											$toInsert[$index] = $id;
										}
									}
									
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
												$label = ($has_header) ? $row[$index] : ''; // Original Label speichern
												$pos = $column_count++;
												$new_headers[] = "($order_list[id], $id, $pos, '$label')";
												$toInsert[$index] = $pos;
											}
											
											$values = join(', ', $new_headers);
											$result = mysql_query_with_error("	INSERT INTO ot_order_list_has_header
																				(order_list_id, header_id, pos, original_label)
																				VALUES $values");
										}
										
										$errors = array();
										$doubles = array();
										$col_ids = array();
										$updates = array();
										$inserts = array();
										while ($row = fgetcsv($f, 0, ';')) {
											$file_row++;
											$match = trim($row[$matching_row]);
											
											if (array_key_exists($match, $toMatch)) {
												$row_id = $toMatch[$match];
												if (array_key_exists($row_id, $doubles)) {
													$errors[] = "($import[id], $file_row, 'Identifier not unique')";
												} else {
													// Updates
													foreach ($toUpdate as $index => $pos) {
														$data = trim($row[$index]);
														$id = $columns[$row_id][$pos];
														$col_ids[] = $id;
														$updates[] = "id = $id THEN '$data'";
													}
													
													// Inserts
													foreach ($toInsert as $index => $pos) {
														$data = trim($row[$index]);
														$inserts[] = "($row_id, $pos, '$data')";
													}
													
													// benutzte IDs speichern
													$doubles[$row_id] = 1;
												}
											} else {
												$errors[] = "($import[id], $file_row, 'Identifier not found')";
											}
										}
											
										if ($updates) {
											$cases = join(' WHEN ', $updates);
											$ids = join(', ', $col_ids);
											$result = mysql_query_with_error("	UPDATE ot_column
																				SET data = CASE
																				WHEN $cases END
																				WHERE id IN ($ids)");
										}
										
										if ($inserts) {
											$values = join(', ', $inserts);
											$result = mysql_query_with_error("INSERT INTO ot_column (row_id, pos, data) VALUES $values");
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
										
										// Import updaten
										$file_row++;
										$result = mysql_query("UPDATE ot_import SET file_row_count=$file_row, stored=1 WHERE id=$import[id]");
										
										mysql_query("COMMIT");
										
										if ($errors) {
											header("Location: index.php?p=import&id=$import[id]");
										} else {
											header("Location: index.php?p=orderlists&id=$order_list[id]");
										}
										
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
								
								$file_row = -1;
								
								// Header auslesen
								$headers = array();
								foreach (preg_grep('/^header_[0-9]+$/', array_keys($_POST)) as $key) {
									$headers[trim($key, 'header_')] = mysql_real_escape_string($_POST[$key]);
								}
								
								// Header anlegen
								$insert = array();
								if ($has_header) {
									$row = fgetcsv($f, 0, ';'); $file_row++;
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
									$file_row++;
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
									$query_s[] = "($id[id], $status[id], $timestamp)";
									foreach ($columns[$i++] as $column) {
										$tmp = trim($column);
										$query_c[] = "($id[id], " . $pos++ . ", '$tmp')";
									}
								}
								
								// Status
								if (!$status['id']) {
									$values = join(', ', $query_s);
									$result = mysql_query("INSERT INTO ot_row_has_order_status (row_id, order_status_id, timestamp_created) VALUES $values");
								}
								
								// Columns
								$values = join(', ', $query_c);
								$result = mysql_query("INSERT INTO ot_column (row_id, pos, data) VALUES $values");
								
								// Import updaten
								$file_row++;
								$result = mysql_query("UPDATE ot_import SET file_row_count=$file_row, stored=1 WHERE id=$import[id]");
								
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
			<?php 
			if ($import['stored']) {
				$errors = array();
				while ($row = mysql_fetch_assoc($result)) {
					$errors[$row['row']] = $row;
				}
			}
			?>
			<form action="index.php?p=import" method="POST" enctype="multipart/form-data">
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
							<td><?php echo create_dropdown_menu('list', 'ot_order_list', 'Neue Liste erstellen', $import['opt_order_list']); ?></td>
						</tr>
						<tr>
							<td>Status</td>
							<td><?php echo create_dropdown_menu('order_status', 'ot_order_status', 'Status beibehalten', $import['opt_order_status']); ?></td>
						</tr>
						<tr>
							<td>Aktualisierung</td>
							<td>
								<?php if ($import['opt_matching']): ?>
									<?php $label = get_row_by_id($import['opt_matching'], 'ot_header'); ?>
									<select name="matching">
										<option value="<?php echo $import['opt_matching']; ?>"><?php echo $label['name']; ?></option>
									</select>
								<?php else: ?>	
									<select name="matching" disabled="disabled">
										<option value="0">Kein Abgleich</option>
									</select>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td colspan=2><input type="submit" value="Datei speichern" <?php if ($import['stored']) echo 'disabled="disabled"'; ?>/></td>
						</tr>
					</table>
				</div>
				<?php if ($import['stored'] && (count($errors) > 0)): ?>
				<input type="hidden" name="action" value="errors" />
				<div>
					<h3>Fehler</h3>
					<table>
						<?php $row = fgetcsv($f, 0, ';'); $pos = 0; $file_row = 0; ?>
						<tr>
							<td>Fehler</td>
						<?php foreach ($row as $column): ?>
							<?php $optional = ($import['opt_has_header']) ? "$column *" : 'Optional'; ?>
							<?php 	$selected = mysql_query("SELECT header_id FROM ot_order_list_has_header WHERE pos=$pos");
									$selected = ($selected = mysql_fetch_assoc($selected)) ? $selected['header_id'] : 0; ?>
							<td><?php echo create_dropdown_menu('header_' . $pos++, 'ot_header', $optional, $selected); ?></td>
						<?php endforeach; ?>
						</tr>
						<?php if (!$import['opt_has_header']): ?>
						<tr>
							<?php if (array_key_exists($file_row, $errors)): ?>
							<td><?php echo $errors[$file_row]['message']; ?></td>
							<?php foreach ($row as $column): ?>
								<td><?php echo $column; ?></td>
							<?php endforeach; ?>
							<?php endif; ?>
						</tr>
						<?php endif; $file_row++; ?>
						<?php while ($row = fgetcsv($f, 0, ';')): ?>
						<?php if (array_key_exists($file_row, $errors)): ?>
						<tr>
							<td>
								<select name="<?php echo 'error_' . $errors[$file_row]['id'] ?>">
									<option value="0"><?php echo $errors[$file_row]['message']; ?></option>
									<option value="1">Zeile verwerfen</option>
									<option value="2">Zeile hinzufügen/aktualisieren</option>
								</select>
							</td>
							<?php foreach ($row as $column): ?>
							<td><?php echo $column; ?></td>
							<?php endforeach; ?>
						</tr>
						<?php endif; $file_row++; ?>
						<?php endwhile; ?>
					</table>
					<input type="submit" value="Fehler korrigieren" />
				</div>
				
				<?php else: ?>
				<input type="hidden" name="action" value="store" />
				<div>
					<h3>Dateiinhalt</h3>
					<table>
						<?php $row = fgetcsv($f, 0, ';'); $pos = 0; $file_row = 0; ?>
						<tr>
						<?php foreach ($row as $column): ?>
							<?php $optional = ($import['opt_has_header']) ? "$column *" : 'Optional'; ?>
							<?php 	$selected = mysql_query("SELECT header_id FROM ot_order_list_has_header WHERE pos=$pos AND order_list_id=$import[opt_order_list]");
									$selected = ($selected = mysql_fetch_assoc($selected)) ? $selected['header_id'] : 0; ?>
							<td class="header"><?php echo create_dropdown_menu('header_' . $pos++, 'ot_header', $optional, $selected); ?></td>
						<?php endforeach; ?>
						</tr>
						<?php if (!$import['opt_has_header']): ?>
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
				<?php endif; ?>
			</form>
			<script type="text/javascript">
				function catch_submit(obj) {
					var optional = 0;
					$('select[name^=header_]').each(function (pos, header) {
						if ($(header).val() == 0) optional++;
					});
					switch (optional) {
						case 0:
							return true;
						case 1:
							return confirm('Für eine Spalte wurde kein Header ausgewählt. Datei wirklich speichern?');
						default:
							return confirm('Für '+optional+' Spalten wurden keine Header ausgewählt. Datei wirklich speichern?');
					}
				};
			
				function add_options(options) {
					var select = $("select[name='matching']");
					$.each(options, function (key, value) {
						var opt = new Array('<option value="', key, '">', value, '</option>');
						$(opt.join('')).appendTo(select);
					});
				};

				function select_headers(headers) {
					var regex = new RegExp('[0-9]+');
					jQuery('.header select').each(function (pos, obj) {
						var select = $(obj);
						var name = select.attr('name');
						var pos = regex.exec(name);
						if (headers[pos]) {
							select.val(headers[pos]);
						} else {
							select.val(0);
						}
					});
				};
				
				function load_matching_options(elem) {
					var id = $(this).children(':selected').attr('value');
					var select = $("select[name='matching']");
					if (id != '0') {
						select.children('*:not([value="0"])').remove();
						select.removeAttr('disabled');
						jQuery.get('ajax/ajax_import.php',
								{action: 'list', id: id},
								add_options,
								'json');
						jQuery.get('ajax/ajax_import.php',
								{action: 'headers', id: id},
								select_headers,
								'json');
					}
					if (id == '0') {
						select.attr('disabled', 'disabled');
					}
				};
				
				$(document).ready(function () {
					$("select[name='list']").bind('change', load_matching_options);
					$('form').bind('submit', catch_submit);
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
				<td>Zeilen</td>
				<td>Erfolgreich</td>
				<td>Fehler</td>
			</tr>
			<?php while ($import = mysql_fetch_assoc($imports)): ?>
				<?php
					$result = mysql_query("SELECT COUNT(*) as error_count FROM ot_import_error WHERE import_id=$import[id]");
					$error_count = mysql_fetch_assoc($result); $error_count = $error_count['error_count'];
				?>
			<tr>
				<td><a href="<?php echo "?p=import&id=$import[id]"?>" ><?php echo $import['id']; ?></a></td>
				<td><a href="<?php echo "?p=import&id=$import[id]"?>" ><?php echo $import['file']; ?></a></td>
				<td><?php echo $import['user_id']; ?></td>
				<td><?php echo date('d.m.Y G:i', $import['timestamp_created']); ?></td>
				<td><input type="checkbox" <?php if ($import['stored']) echo 'checked'; ?> /></td>
				<td><?php echo ($import['stored']) ? $import['file_row_count'] : 'Unbekannt'; ?></td>
				<td><?php echo ($import['stored']) ? $import['file_row_count'] - $error_count : 'Unbekannt'; ?></td>
				<td><?php echo ($import['stored']) ? $error_count : 'Unbekannt'; ?></td>
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