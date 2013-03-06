<?php
$result = mysql_query("	SELECT *
						FROM ot_attribute_set") or die('MYSQLError: ' . mysql_error());
$sets = array();
while ($row = mysql_fetch_assoc($result)) {
	$sets[] = $row;
} ?>

<h1>Attribut-Sets</h1>

<?php if ($sets): ?>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($sets as $row): ?>
			<tr>
				<td><?php echo $row['id']; ?></td>
				<td><a href="<?php echo $ot->get_link('settings', $row['id'], 'attributeset'); ?>"><?php echo $row['name']; ?></a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	
<?php else: ?>
	<p>Keine Attribut-Sets vorhanden!</p>

<?php endif; ?>

<button id="add_attributeset_button">Attribut-Set anlegen</button>

<div id="add_attributeset_dialog">
	<form id="add_attributeset_form" action="<?php echo $ot->get_link('settings', 0, 'attributeset'); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="add" />
		<label for="name">Name: </label>
		<input type="text" name="name" />
	</form>
</div>

<script>
	jQuery(document).ready(function () {
		var add_attributeset_dialog = $('#add_attributeset_dialog');
		
		add_attributeset_dialog.dialog({
			title: 'Attribut-Set anlegen',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Anlegen': function () {
					$('#add_attributeset_form').submit();
				}
			}
		});

		$('#add_attributeset_button').bind('click', function () {
			add_attributeset_dialog.dialog('open');
		});
	});
</script>