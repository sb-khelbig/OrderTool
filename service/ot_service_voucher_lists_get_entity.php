<?php $voucher_list = VoucherList::get($_GET['id']); ?>

<h1><?php echo $voucher_list->name; ?></h1>

<form>
	<input type="hidden" name="action" value="import" />
	<fieldset>
		<legend>Import</legend>
		<input id="import-csv-button" type="button" name="source" value="CSV-Datei" />
		<input type="button" name="source" value="Shopware" />
	</fieldset>
</form>

<?php $codes = $voucher_list->codes->all(); ?>
<form>
	<fieldset>
		<legend>Codes (<?php echo count($codes); ?>)</legend>
		<?php if ($codes): ?>
			<table>
				<thead>
					<tr>
						<th>Code</th>
						<th>Position</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($codes as $code): ?>
						<tr>
							<td><?php echo $code->code; ?></td>
							<td><?php echo $code->position; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		
		<?php else: ?>
			<p>Keine Codes vorhanden!</p>
			
		<?php endif; ?>
	</fieldset>
</form>

<div id="import-csv-dialog">
	<form id="import-csv-form" action="<?php echo $ot->get_link('service', $voucher_list->id, 'voucher_lists'); ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="import_csv" />
		<input type="file" name="csv" />
	</form>
</div>

<script>
	jQuery(document).ready(function () {
		var dialog = $('#import-csv-dialog');

		dialog.dialog({
			title: 'CSV-Import',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Importieren': function () {
					$('#import-csv-form').submit();
				}
			}
		});
		
		$('#import-csv-button').bind('click', function () {
			dialog.dialog('open');
		});
	});	
</script>