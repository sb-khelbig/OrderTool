<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
		if (isset($_GET['id'])) {
			if ($order_list = get_row_by_id($_GET['id'], 'ot_order_list')) {
				switch ($action) {
					case 'change_name':
						$name = mysql_real_escape_string($_POST['name']);
						$result = mysql_query("UPDATE ot_order_list SET name='$name' WHERE id=$order_list[id]");
						if (mysql_affected_rows() != -1) {
							log_action(3, $order_list['id']);
							echo "Name erfolgreich geändert!";
						} else {
							echo "Name konnte nicht geändert werden!";
						}
						header("Location: index.php?p=orderlists&id=$order_list[id]");
						break;
					case 'change_header':
						foreach (preg_grep('/^header_[0-9]+$/', array_keys($_POST)) as $key) {
							$id = trim($key, 'header_');
							$header_id = mysql_real_escape_string($_POST[$key]);
							$result = mysql_query("UPDATE ot_order_list_has_header SET header_id=$header_id WHERE id=$id");
						}
						log_action(5, $order_list['id']);
						header("Location: index.php?p=orderlists&id=$order_list[id]");
						break;
					default:
						header("Location: index.php?p=orderlists&id=$order_list[id]");
				}
			}
		}
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php if ($order_list = get_row_by_id($_GET['id'], 'ot_order_list')): ?>
			<form action="<?php echo "index.php?p=orderlists&id=$order_list[id]"; ?>" method="POST" enctype="multipart/form-data">
				<input type="hidden" value="change_name" name="action" />
				<input type="text" value="<?php echo $order_list['name']; ?>" name="name" style="padding-left: 2px; width: 350" />
				<input type="submit" value="Namen ändern" />
			</form>
			<?php
			$result = mysql_query("SELECT id, header_id, original_label FROM ot_order_list_has_header WHERE order_list_id=$order_list[id]");
			$headers = array();
			while ($header = mysql_fetch_assoc($result)) {
				$headers[] = $header;
			}
			$columns = mysql_query("SELECT * FROM ot_column WHERE row_id in (SELECT id FROM ot_row WHERE order_list_id=$order_list[id]) ORDER BY row_id, pos");
			$last_row_id = 0;
			?>
			<form action="<?php echo "index.php?p=orderlists&id=$order_list[id]"; ?>" method="POST" enctype="multipart/form-data">
				<input type="hidden" value="change_header" name="action" />
				<input type="submit" value="Header speichern" />
				<table>
					<tr>
						<td>ID</td>
						<?php foreach ($headers as $h): ?>
						<td><?php echo create_dropdown_menu("header_$h[id]", 'ot_header', "$h[original_label] *", $h['header_id']); ?></td>
						<?php endforeach; ?>
					<?php while ($column = mysql_fetch_assoc($columns)): ?>
					<?php if ($column['row_id'] > $last_row_id): ?>
					</tr>
					<tr>
						<td><a href="<?php echo "?p=order&id=$column[row_id]"; ?>" ><?php echo $column['row_id']; ?></a>
					<?php endif; $last_row_id=$column['row_id']; ?>
						<td><?php echo $column['data']; ?></td>
					<?php endwhile; ?>
					</tr>
				</table>
			</form>
		<?php else: ?>
			<p>ID existiert nicht!</p>
		<?php endif; ?>
		
	<?php else: ?>
		<h1>Bestelllisten</h1>
		<?php $result = mysql_query("SELECT * FROM ot_order_list LIMIT 20"); ?>
		<table>
			<?php while ($row = mysql_fetch_assoc($result)): ?>
				<tr>
					<td><?php echo $row['id']; ?></td>
					<td><a href="<?php echo "?p=orderlists&id=$row[id]"; ?>"><?php echo $row['name']; ?></a></td>
				</tr>
			<?php endwhile; ?>
		</table>
	<?php endif; ?>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>