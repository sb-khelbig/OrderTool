<?php include ('db/connection.php'); include ('functions/html.php')?>

<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>

	<?php 
	if ($_FILES['file']['tmp_name']) {
		$result = mysql_query("SET GLOBAL max_allowed_packet=16777216");
		
		$timestamp = time();
		$result = mysql_query("INSERT INTO ot_import (timestamp_created) VALUES ($timestamp)");
		$import_id = mysql_insert_id();
		
		if ($_POST['list']) {
			$order_list_id = $_POST['list'];
		} else {
			$result = mysql_query("INSERT INTO ot_order_list (name) VALUES ('Unbenannte Liste $timestamp')");
			$order_list_id = mysql_insert_id();
		}
		
		if ($f = fopen($_FILES['file']['tmp_name'], 'r')) {
			$rows = array();
			$columns = array();
			if (isset($_POST['has_header'])) $header = fgetcsv($f, 0, ';');
			
			while ($row = fgetcsv($f, 0, ';')) {
				$rows[] = "($order_list_id, $import_id)";
				$columns[] = $row;
			}
			
			$result = mysql_query("INSERT INTO ot_row (order_list_id, import_id) VALUES " . join(', ', $rows));
			$result = mysql_query("SELECT id FROM ot_row WHERE import_id=$import_id");
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
			Datei hat Spaltennamen: <input type="checkbox" name="has_header" value="1" checked />  <br />
			<input type="submit" value="Senden" />
		</form>
	</div>
<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>