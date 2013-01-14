<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'add_status':
				$name = mysql_real_escape_string($_POST['name']);
				$result = mysql_query("INSERT INTO ot_order_status (name) VALUES ('$name')");
				if ($id = mysql_insert_id()) {
					echo "Status added!";
					header("Location: index.php?p=status&id=$id");
				} else {
					echo "Status could not be added!";
					header("Location: index.php?p=status");
				}
				break;
			case 'change_name':
				if (isset($_GET['id'])) {
					if ($status = get_row_by_id($_GET['id'], 'ot_order_status')) {
						$name = mysql_real_escape_string($_POST['name']);
						$result = mysql_query("UPDATE ot_order_status SET name='$name' WHERE id=$status[id]");
						if (mysql_affected_rows() != -1) {
							echo "Name erfolgreich geändert!";
						} else {
							echo "Name konnte nicht geändert werden!";
						}
						header("Location: index.php?p=status&id=$status[id]");
					}
				}
				break;
		}
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php if ($status = get_row_by_id($_GET['id'], 'ot_order_status')): ?>
			<form action="<?php echo "index.php?p=status&id=$status[id]"; ?>" method="POST" enctype="multipart/form-data">
				<input type="hidden" value="change_name" name="action" />
				<input type="text" value="<?php echo $status['name']; ?>" name="name" style="padding-left: 2px;" />
				<input type="submit" value="Namen ändern" />
			</form>
		
		<?php else: ?>
			<p>ID existiert nicht!</p>
		<?php endif; ?>
		
	<?php else: ?>
		<h1>Status</h1>
		<?php $statuses = mysql_query("SELECT * FROM ot_order_status"); ?>
		<table>
			<?php while ($status = mysql_fetch_assoc($statuses)): ?>
			<tr>
				<td><?php echo $status['id']; ?></td>
				<td><a href="<?php echo "index.php?p=status&id=$status[id]"; ?>" ><?php echo $status['name']; ?></a></td>
			</tr>
			<?php endwhile; ?>
		</table>
		<br />
		<div>
			<form action="index.php?p=status" method="POST" enctype="multipart/form-data">
				
				<input type="hidden" name="action" value="add_status" />
				<input type="text" name="name" />
				<input type="submit" value="Status anlegen" />
			</form>
		</div>
	
	<?php endif; ?>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>