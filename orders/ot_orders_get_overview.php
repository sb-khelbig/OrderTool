<?php

$query = "	SELECT id, short_name
			FROM ot_attribute
			WHERE opt_pos_in_overview > 0
			ORDER BY opt_pos_in_overview";

$result = MySQL::query($query);

$attributes = array();

while ($row = MySQL::fetch($result)) {
	$attributes[$row['id']] = $row['short_name'];
}

$query = "	SELECT *
			FROM ot_order";

$result = MySQL::query($query);

$orders = array();
$customers = array();

while ($row = MySQL::fetch($result)) {
	$orders[$row['id']] = array();
	$customers[$row['customer_id']][] = $row['id'];
}

$data = array();

if ($orders) {
	
	// Orders
	$query = build_select('ot_order', array_keys($orders));
	
	$result = MySQL::query($query);
	
	while ($row = MySQL::fetch($result)) {
		foreach ($row as $key => $value) {
			$data[$row['ref_id']][$key] = $value;
		}
	}
	
	// Customers
	$query = build_select('ot_customer', array_keys($customers));
	
	$result = MySQL::query($query);
	
	while ($row = MySQL::fetch($result)) {
		foreach ($customers[$row['ref_id']] as $order) {
			foreach ($row as $key => $value) {
				$data[$order][$key] = $value;
			}
		}
	} 
} ?>

<h1>Bestellungen</h1>

<?php if ($data): ?>
	<table id="orders">
		<thead>
			<tr>
				<?php foreach ($attributes as $id => $name): ?>
					<th><?php echo $name; ?></th>
				<?php endforeach; ?>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<tr class="spacing"><td></td></tr>
			<?php foreach ($data as $id => $values): ?>
				<tr class="order" id="<?php echo $id; ?>">
					<?php foreach ($attributes as $id => $name): ?>
						<td><?php echo isset($values[$id]) ? $values[$id] : '&nbsp;'; ?></td>
					<?php endforeach;?>
					<td>Bezahlt</td>
				</tr>
				<tr class="spacing"><td></td></tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
<?php else: ?>
	<p>Keine Bestellungen vorhanden!</p>
	
<?php endif; ?>

<?php include 'ticket/ot_ticket_dialog.php'; ?>

<script>
	var dialog = null;
	
	function fill_dialog(data) {
		jQuery.each(data, function (key, value) {
			$('*[name=' + key + ']', dialog).val(value);
		});
		dialog.dialog('open');
	};
	
	jQuery(document).ready(function () {
		var loading = false;
		dialog = $('#create_ticket_dialog');
		
		dialog.dialog({
			title: 'Ticket erstellen',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Erstellen': function () {
					$('#create_ticket_form').submit();
				}
			}
		});
		
		$('.order').bind('click', function () {
			if (!loading) {
				loading = true;
				var order = $(this);
				var id = order.attr('id');
				var info = $('#info_' + id);
				if (info.length) {
					info.slideToggle(0);
				} else {
					$.get('orders/ot_orders_ajax.php',
							{id: id, action: 'load'},
							function (data) {
								var tr = $('<tr id="info_' + id + '" style="display: none; font-size: 10px;">');
								var td = $('<td>'); td.attr('colspan', order.children().length); tr.append(td);
								
								if (!data['error']) {
									var tabs = $('<div>'); td.append(tabs);
									var ul = $('<ul>'); tabs.append(ul);
	
									$.each(data['data'], function (index, tab) {
										ul.append('<li><a href="#' + tab['id'] + '">' + tab['name'] + '</a></li>');
										var div = $('<div id="' + tab['id'] + '"></div>');
										div.append(tab['content']);
										tabs.append(div);
									});
	
									// jQuery UI Tabs
									tabs.tabs({active: 2});
									
								} else {
									tr.addClass('error');
									td.append('<p>Inhalt konnte nicht geladen werden. Fehlermeldung: ' + data['errorMsg'] + '</p>');
								}
								
								// Insert new tr
								order.after(tr);
	
								// Show
								tr.slideDown(0);
							},
							'json');
				};
				loading = false;
			};
		});
	});
</script>