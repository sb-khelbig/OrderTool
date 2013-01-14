<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	// TODO: echos durch messageframework ersetzen
	if (isset($_GET['id'])) {
		if ($order = get_row_by_id($_GET['id'], 'ot_row')) {
			if (isset($_POST['action'])) {
				switch ($_POST['action']) {
					case 'add_note':
						$data = mysql_real_escape_string($_POST['data']);
						$result = mysql_query("INSERT INTO ot_note (user_id, ref_table, ref_id, data) VALUES ($_SESSION[UserID], 'ot_row', $order[id], '$data')");
						if (mysql_insert_id()) {
							echo "Notiz gespeichert!";
						} else {
							echo "Notiz konnte nicht gespeichert werden!";
						}
						header("Location: index.php?p=order&id=$order[id]");
						break;
					case 'change_status':
						$status_id = mysql_real_escape_string($_POST['status']);
						$timestamp = time();
						$result = mysql_query("INSERT INTO ot_row_has_order_status (user_id, row_id, order_status_id, timestamp_created) VALUES ($_SESSION[UserID], $order[id], $status_id, $timestamp)");
						if (mysql_insert_id()) {
							echo "Status geändert!";
						} else {
							echo "Status konnte nicht geändert werden!";
						}
						header("Location: index.php?p=order&id=$order[id]");
						break;
					default:
						echo "Unsupported action!";
						header("Location: index.php?p=order&id=$order[id]");
				}
			} else {
				echo "No action set!";
				header("Location: index.php?p=order&id=$order[id]");
			}
		} else {
			echo "ID existiert nicht!";
			header("Location: index.php?p=orderlists");
		}
	} else {
		echo "No ID set!";
		header("Location: index.php?p=orderlists");
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php if ($order = get_row_by_id($_GET['id'], 'ot_row')): ?>
			<h1>Bestellung <?php echo $order['id']; ?></h1>
			
			<?php // Status
			$result = mysql_query("SELECT order_status_id as id FROM ot_row_has_order_status WHERE row_id=$order[id] ORDER BY timestamp_created DESC LIMIT 1");
			$status = ($s = mysql_fetch_assoc($result)) ? $s['id'] : '0'; ?>
			<form action="<?php echo "index.php?p=order&id=$order[id]"; ?>" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="action" value="change_status" />
				<label for="status">Status: </label>
				<?php echo create_dropdown_menu('status', 'ot_order_status', 'Unbekannt', $status); ?>
				<input type="submit" value="Status ändern" />
			</form>
			
			<?php // Columns
			$columns = mysql_query("SELECT * FROM ot_column WHERE row_id=$order[id]");
			$result = mysql_query("SELECT header_id as id FROM ot_order_list_has_header WHERE order_list_id=$order[order_list_id]");
			$headers = array();
			while ($row = mysql_fetch_assoc($result)) {
				$header = mysql_query("SELECT * FROM ot_header WHERE id=$row[id]");
				$headers[] = ($h = mysql_fetch_assoc($header)) ? $h['name'] : 'Optional';
			} ?>
			<form>
				<?php while ($column = mysql_fetch_assoc($columns)): ?>
				<label for="<?php echo $column['id']; ?>"><?php echo $headers[$column['pos']]; ?>:</label>
				<input type="text" name="<?php echo $column['id']; ?>" value="<?php echo $column['data']; ?>" size="100" /> <br />
				<?php endwhile; ?>
			</form>
			
			<?php // Notes
			$notes = mysql_query("SELECT id, data FROM ot_note WHERE ref_table='ot_row' AND ref_id=$order[id]"); ?>
			<div class="notes">
				<h3>Notizen</h3>
				<?php while ($note = mysql_fetch_assoc($notes)): ?>
				<div class="note" id="note_<?php echo $note['id']; ?>">
					<textarea><?php echo $note['data']; ?></textarea>
				</div>
				<?php endwhile; ?>
				<form action="<?php echo "index.php?p=order&id=$order[id]"; ?>" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="action" value="add_note" />
					<textarea name="data"></textarea><br />
					<input type="submit" value="Notiz anheften" />
				</form>
			</div>
		
		<?php else: ?>
			<p>ID existiert nicht!</p>
			
		<?php endif; ?>
	
	<?php endif; ?>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>

<?php endif; ?>