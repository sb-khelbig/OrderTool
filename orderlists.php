<?php include ('db/connection.php'); include ('db/functions.php')?>

<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<p>TODO</p>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php $order_list = get_row_by_id($_GET['id'], 'ot_order_list'); ?>
		<?php if ($order_list): ?>
			<h1><?php echo $order_list['name']; ?></h1>
			<?php
			$limit_start = 0; $limit_end = 100;
			$result = mysql_query("SELECT id FROM ot_row WHERE order_list_id=$order_list[id] LIMIT $limit_start, $limit_end");
			$rows = array();
			while ($row = mysql_fetch_assoc($result)) {
				$rows[] = $row['id'];
			}
			$rows = join(', ', $rows);
			$columns = mysql_query("SELECT * FROM ot_column WHERE row_id in ($rows)");
			$last_row_id = 0;
			?>
			<table>
				<tr>
				<?php while ($column = mysql_fetch_assoc($columns)): ?>
				<?php if ($column['row_id'] > $last_row_id) echo '</tr><tr>'; $last_row_id=$column['row_id']; ?>
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
					<td><a href="<?php echo "?p=orderlists&id=$row[id]";?>"><?php echo $row['name'];?></a></td>
				</tr>
			<?php endwhile; ?>
		</table>
	<?php endif; ?>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>