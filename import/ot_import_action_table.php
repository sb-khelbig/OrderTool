<table>
<?php if ($has_header): ?>
	<?php $row_count = count($row_ids); ?>
	<thead>
		<tr>
		<?php foreach ($rows[$row_ids[0]] as $pos => $column): ?>
			<th><?php echo create_dropdown_menu("headers[$pos]", 'ot_header', "$column *"); ?></th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
	<?php for ($i = 1; $i < $row_count; $i++): ?>
		<tr>
		<?php foreach ($rows[$row_ids[$i]] as $pos => $column): ?>
			<td><?php echo $column; ?></td>
		<?php endforeach; ?>
		</tr>
	<?php endfor; ?>
	</tbody>

<?php else: ?>
	<thead>
		<tr>
		<?php foreach ($rows[$row_ids[0]] as $pos => $column): ?>
			<th><?php echo create_dropdown_menu("headers[$pos]", 'ot_header', 'Optional'); ?></th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($rows as $row_id => $row): ?>
		<tr>
		<?php foreach ($row as $pos => $column): ?>
			<td><?php echo $column; ?></td>
		<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>

<?php endif; ?>
</table>
<?php if ($limit == count($rows)): ?>
	<p style="font-size: small;">Es wird nicht die gesamte Datei angezeigt.</p>
<?php else: ?>
	<br />
<?php endif;?>