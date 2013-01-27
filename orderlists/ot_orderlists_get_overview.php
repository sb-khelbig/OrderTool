<?php
$result = mysql_query("SELECT * FROM ot_order_list LIMIT 20");
?>
<h1>Bestelllisten</h1>
<table>
	<?php while ($row = mysql_fetch_assoc($result)): ?>
		<tr>
			<td><?php echo $row['id']; ?></td>
			<td><a href="<?php echo "?p=orderlists&id=$row[id]"; ?>"><?php echo $row['name']; ?></a></td>
		</tr>
	<?php endwhile; ?>
</table>