<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>

	<?php 
	if ($_FILES['file']['tmp_name']) {
		$result = mysql_query("SET GLOBAL max_allowed_packet=16777216");
		
		// Import anlegen
		$timestamp = time();
		$result = mysql_query("INSERT INTO ot_import (timestamp_created) VALUES ($timestamp)");
		$import_id = mysql_insert_id();
		
		// Order_List anlegen / abfragen
		if ($_POST['list']) {
			$order_list_id = $_POST['list']; // TODO: id überprüfen
			$new_list = False;
		} else {
			$result = mysql_query("INSERT INTO ot_order_list (name) VALUES ('Unbenannte Liste $timestamp')");
			$order_list_id = mysql_insert_id();
			$new_list = True;
		}
		
		if ($f = fopen($_FILES['file']['tmp_name'], 'r')) {
			$rows = array(); $columns = array(); $insert = array(); $pos = 0;
			
			// Header anlegen
			$header = fgetcsv($f, 0, ';');
			if (isset($_POST['has_header'])) {
				foreach ($header as $label) {
					$insert[] = "($order_list_id, " . $pos++ . ", '$label')";
				}
			} else {
				foreach ($header as $label) {
					$insert[] = "($order_list_id, " . $pos++ . ", '')";
				}
				$rows[] = "($order_list_id, $import_id)";
				$columns[] = $header;
			}
			
			// bei neuer Liste Header speichern, ansonsten Anzahl und Titel überprüfen
			if ($new_list) {
				$values = join(', ', $insert);
				$result = mysql_query("INSERT INTO ot_order_list_has_header (order_list_id, pos, original_label) VALUES $values") or die(mysql_error());
			} else {
				$result = mysql_query("SELECT pos, original_label FROM ot_order_list_has_header WHERE order_list_id=$order_list_id");
				
				if (mysql_num_rows($result) != count($header)) {
					$error = "Spaltenzahl stimmt nicht mit vorherigen Listen überein!";
					$result = mysql_query("UPDATE ot_import SET error='$error' WHERE id=$import_id");
					die($error);
				}
				
				if (isset($_POST['has_header'])) {
					while ($row = mysql_fetch_assoc($result)) {
						if ($header[$row['pos']] != $row['original_label']) {
							$error = "Spaltenbezeichnungen stimmen nicht mit vorherigen Listen überein!";
							$result = mysql_query("UPDATE ot_import SET error='$error' WHERE id=$import_id");
							die($error);
						}
					}
				}
			}
			
			// Rows anlegen
			while ($row = fgetcsv($f, 0, ';')) {
				$rows[] = "($order_list_id, $import_id)";
				$columns[] = $row;
			}
			
			$result = mysql_query("INSERT INTO ot_row (order_list_id, import_id) VALUES " . join(', ', $rows));
			$result = mysql_query("SELECT id FROM ot_row WHERE import_id=$import_id");
			
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
			
			$result = mysql_query("INSERT INTO ot_column (row_id, pos, data) VALUES " . join(', ', $query)) or die(mysql_error());
			
			fclose($f);
			
			header("Location: index.php?p=orderlists&id=$order_list_id");
			
		} else {
			echo 'Dateifehler!';
		}
		
	} else {
		echo 'Keine Datei angegeben!';
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<div>
		<form action="index.php?p=import" method="POST" enctype="multipart/form-data">
			Liste: <?php echo create_dropdown_menu('list', 'ot_order_list', 'Neue Liste erstellen'); ?> <br />
			Datei: <input type="file" name="file" /> <br />
			Datei hat Spaltenbezeichnungen: <input type="checkbox" name="has_header" value="1" checked />  <br />
			<input type="submit" value="Senden" />
		</form>
	</div>
<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>