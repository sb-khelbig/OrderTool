<?php include ('db/connection.php'); include ('db/functions.php')?>

<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	$order_list = get_row_by_id($_POST['id'], 'ot_order_list');
	if ($order_list) {
		$name = mysql_real_escape_string($_POST['name']);
		$result = mysql_query("UPDATE ot_order_list SET name='$name' WHERE id=$order_list[id]");
		header("Location: index.php?p=orderlists&id=$order_list[id]");
	} else {
		header("Location: index.php?p=orderlists");
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php $order_list = get_row_by_id($_GET['id'], 'ot_order_list'); ?>
		<?php if ($order_list): ?>
			<form action="index.php?p=orderlists" method="POST" enctype="multipart/form-data">
				<input type="hidden" value="<?php echo $order_list['id']; ?>" name="id" />
				<input type="text" value="<?php echo $order_list['name']; ?>" name="name" style="padding-left: 2px;" />
				<input type="submit" value="Namen ändern" />
			</form>
			<?php
			/*$limit_start = 0; $limit_end = 100;
			$result = mysql_query("SELECT id FROM ot_row WHERE order_list_id=$order_list[id]");// LIMIT $limit_start, $limit_end");
			$rows = array();
			while ($row = mysql_fetch_assoc($result)) {
				$rows[] = $row['id'];
			}
			$rows = join(', ', $rows);
			$columns = mysql_query("SELECT * FROM ot_column WHERE row_id in ($rows)");*/
			$header = mysql_query("SELECT id, original_label FROM ot_order_list_has_header WHERE order_list_id=$order_list[id]");
			$columns = mysql_query("SELECT * FROM ot_column WHERE row_id in (SELECT id FROM ot_row WHERE order_list_id=$order_list[id])");
			$last_row_id = 0;
			?>
			<table>
				<tr>
				<?php while ($h = mysql_fetch_assoc($header)): ?>
					<td><?php echo $h['original_label']; ?> </td>
				<?php endwhile; ?>
				<?php while ($column = mysql_fetch_assoc($columns)): ?>
				<?php if ($column['row_id'] > $last_row_id): ?>
				</tr>
				<tr>
				<?php endif; $last_row_id=$column['row_id']; ?>
					<td><?php echo $column['data']; ?></td>
				<?php endwhile; ?>
				</tr>
			</table>
		<?php else: ?>
			<p>ID existiert nicht!</p>
		<?php endif; ?>
		
	<?php else: ?>
		<h1>Bestelllisten</h1>
		<?php $result = mysql_query("SELECT * FROM ot_order_list LIMIT 20"); ?>
		<table>
			<?php while ($row = mysql_fetch_assoc($result)): ?>
				<tr>
					<td><?php echo $row['id'];?></td>
					<td><a href="<?php echo "?p=orderlists&id=$row[id]";?>"><?php echo $row['name']; ?></a></td>
				</tr>
			<?php endwhile; ?>
		</table>
	<?php endif; ?>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>