<?php $orders = Order::all(); ?>

<h1>Bestellungen</h1>

<?php if ($orders): ?>
	<table id="orders">
		<thead>
			<tr>
				<th>ID</th>
				<th>Irgend</th>
				<th>was</th>
				<th>anderes</th>
			</tr>
		</thead>
		<tbody>
			<tr class="spacing"><td></td></tr>
	<?php foreach ($orders as $order): ?>
			<tr class="order" id="<?php echo $order->id; ?>">
				<td><?php echo $order->id; ?></td>
				<td>pi</td>
				<td>pa</td>
				<td>po</td>
			</tr>
			<tr class="spacing"><td></td></tr>
	<?php endforeach; ?>
		</tbody>
	</table>
	
<?php else: ?>
	<p>Keine Bestellungen vorhanden!</p>
	
<?php endif; ?>

<script>
	jQuery(document).ready(function () {
		$('.order').bind('click', function () {
			var order = $(this);
			var id = order.attr('id');
			var info = $('#info_' + id);
			if (info.length) {
				info.slideToggle(0);
			} else {
				$.get('orders/ot_orders_ajax.php',
						{id: id, action: 'load'},
						function (data) {
							var tr = $('<tr id="info_' + id + '" style="display: none">');
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
								tabs.tabs();
								
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
		});
	});
</script>