<?php include ('db/connection.php'); include ('db/functions.php')?>

<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<p>TODO</p>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php if (isset($_GET['id'])): ?>
		<?php $order_list = get_row_by_id($_GET['id'], 'ot_order_list'); ?>
		<?php if ($order_list): ?>
			<h1><?php echo $order_list['name']; ?></h1>
			<?php $rows = mysql_query("SELECT * FROM ot_row WHERE order_list_id=$order_list[id] LIMIT 10") or die(mysql_error()); ?>
			<table>
			<?php while ($row = mysql_fetch_assoc($rows)): ?>
				<?php $columns = mysql_query("SELECT * FROM ot_column WHERE row_id=$row[id]") or die(mysql_error()); ?>
				<?php echo mysql_num_rows($columns); ?>
				<tr>
					<?php while ($column = mysql_fetch_assoc($columns)): ?>
					<td><?php echo $column['data']; ?></td>
					<?php endwhile; ?>
				</tr>
			<?php endwhile; ?>
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