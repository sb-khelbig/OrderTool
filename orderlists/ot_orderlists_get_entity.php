<?php
$order_list = get_row_by_id($_GET['id'], 'ot_order_list') or die("ID existiert nicht!");

// Headers
$result = mysql_query("	SELECT id, header_id, original_label
						FROM ot_order_list_has_header
						WHERE order_list_id=$order_list[id]");
$headers = array();
while ($header = mysql_fetch_assoc($result)) {
	$headers[] = $header;
}

// Rows
$result = mysql_query("	SELECT r.id, c.pos, c.data
						FROM ot_column AS c, ot_row AS r
						WHERE c.row_id = r.id
							AND r.order_list_id = $order_list[id]
						ORDER BY r.id, c.pos");
$rows = array();
while ($row = mysql_fetch_assoc($result)) {
	if (array_key_exists($row['id'], $rows)) {
		$rows[$row['id']][$row['pos']] = $row['data'];
	} else {
		$rows[$row['id']] = array($row['pos'] => $row['data']);
	}
} ?>

<form id="changename" action=<?php echo "index.php?p=orderlists&id=$order_list[id]"; ?> method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="changename" />
<input type="text" value="<?php echo $order_list['name']; ?>" name="name" style="padding-left: 2px; width: 350" />
<input type="submit" value="Namen ändern" />
</form>

<form id="changeheader" action=<?php echo "index.php?p=orderlists&id=$order_list[id]"; ?> method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="changeheader" />
	<input type="submit" value="Header speichern" />
	<table>
		<tr>
			<td>ID</td>
			<?php foreach ($headers as $h): ?>
				<td><?php echo create_dropdown_menu("headers[$h[id]]", 'ot_header', "$h[original_label] *", $h['header_id']); ?></td>
			<?php endforeach; ?>
		</tr>
		<?php foreach ($rows as $row_id => $columns): ?>
		<tr id="<?php echo $row_id; ?>">
			<td><a href="<?php echo "?p=order&id=$row_id"; ?>" ><?php echo $row_id; ?></a></td>
			<?php foreach ($columns as $pos => $column): ?>
				<td><?php echo $column; ?></td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</table>
</form>

<script>
	function post_request(event) {
		event.preventDefault();
		
		var form = $(this);
		
		jQuery.post(
				form.attr('action'),
				form.serialize(),
				function (data, textStatus, jqXHR) {
					var html = $.parseHTML(data);
					var result = $.parseJSON($('#ajax_result', html).text());
					if (result) {
						alert("Erfolgreich geändert.");
					} else {
						alert("Fehler!");
					}
				},
				'html');
	};
	
	jQuery(document).ready(function () {
		$('#changename').submit(post_request);
		$('#changeheader').submit(post_request);
	});
</script>