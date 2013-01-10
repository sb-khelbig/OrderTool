<?php include ('db/connection.php'); ?>
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
			$result = mysql_query('INSERT INTO ot_order_list VALUES ()');
			$order_list_id = mysql_insert_id();
		}
		
		if ($f = fopen($_FILES['file']['tmp_name'], 'r')) {
			$rows = array();
			$columns = array();
			while ($row = fgetcsv($f, 0, ';')) {
				$rows[] = "($order_list_id, $import_id)";
				$columns[] = $row;
			}
			
			$result = mysql_query("INSERT INTO ot_row (order_list_id, import_id) VALUES " . join(', ', $rows));
			$result = mysql_query("SELECT id FROM ot_row WHERE import_id=$import_id");
			$i = 0;
			$query = array();
			while ($id = mysql_fetch_assoc($result)) {
				foreach ($columns[$i] as $column) {
					$tmp = trim($column);
					$query[] = "($id[id], '$tmp')";
				}
				$i++;
			}
			
			$result = mysql_query("INSERT INTO ot_column (row_id, data) VALUES " . join(', ', $query)) or die(mysql_error());
			
			fclose($f);
			
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
			<select name="list">
				<option value="0">Neue Liste erstellen</option>
				<?php $result = mysql_query('SELECT id, name FROM ot_order_list');
				while ($row = mysql_fetch_assoc($result)) {
					echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
				} ?>
			</select>
			<input type="file" name="file" />
			<input type="submit" value="Senden" />
		</form>
	</div>
<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>