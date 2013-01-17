<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'add_header':
				$name = mysql_real_escape_string($_POST['name']);
				$result = mysql_query("INSERT INTO ot_header (name) VALUES ('$name')") or die(mysql_error());
				if ($id = mysql_insert_id()) {
					echo "Header added!";
					log_action(7, $id);
					header("Location: index.php?p=header&id=$id");
				} else {
					echo "Header could not be added!";
					header("Location: index.php?p=header");
				}
				break;
			case 'change_name':
				if (isset($_GET['id'])) {
					if ($header = get_row_by_id($_GET['id'], 'ot_header')) {
						$name = mysql_real_escape_string($_POST['name']);
						$result = mysql_query("UPDATE ot_header SET name='$name' WHERE id=$header[id]");
						if (mysql_affected_rows() != -1) {
							echo "Name erfolgreich geändert!";
							log_action(8, $header['id']);
						} else {
							echo "Name konnte nicht geändert werden!";
						}
						header("Location: index.php?p=header&id=$header[id]");
					}
				}
				break;
		}
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php if ($header = get_row_by_id($_GET['id'], 'ot_header')): ?>
			<form action="<?php echo "index.php?p=header&id=$header[id]"; ?>" method="POST" enctype="multipart/form-data">
				<input type="hidden" value="change_name" name="action" />
				<input type="text" value="<?php echo $header['name']; ?>" name="name" style="padding-left: 2px;" />
				<input type="submit" value="Namen ändern" />
			</form>
		
		<?php else: ?>
			<p>ID existiert nicht!</p>
		<?php endif; ?>
		
	<?php else: ?>
		<h1>Header</h1>
		<?php $headers = mysql_query("SELECT * FROM ot_header"); ?>
		<table>
			<?php while ($header = mysql_fetch_assoc($headers)): ?>
			<tr>
				<td><?php echo $header['id']; ?></td>
				<td><a href="<?php echo "index.php?p=header&id=$header[id]"; ?>" ><?php echo $header['name']; ?></a></td>
			</tr>
			<?php endwhile; ?>
		</table>
		<br />
		<div>
			<form action="index.php?p=header" method="POST" enctype="multipart/form-data">
				
				<input type="hidden" name="action" value="add_header" />
				<input type="text" name="name" />
				<input type="submit" value="Header anlegen" />
			</form>
		</div>
	
	<?php endif; ?>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>