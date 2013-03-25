<div id="api_shopware_tabs">
	<ul>
		<li><a href="#api_shopware_tabs_start">Start</a></li>
		<li><a href="#api_shopware_tabs_settings">Settings</a></li>
		<li><a href="#api_shopware_tabs_attributes">Attribute</a></li>
		<li><a href="#api_shopware_tabs_import">Import</a></li>
	</ul>
	<div id="api_shopware_tabs_start">
		Start
	</div>
	<div id="api_shopware_tabs_settings">
		<div id="api_shopware_settings_accordion">
			<h3>Allgemein</h3>
			<div>
				<form action="<?php echo $ot->get_link("data_source", $data_source->id); ?>" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="action" value="api" />
					<input type="hidden" name="api_action" value="save_settings_general" />
					<?php foreach ($data_source->getOptionsArray() as $name => $value): ?>
						<input type="hidden" name="setting_name[]" value="<?php echo $name; ?>">
						<label for="setting_value[]"><?php echo $name; ?>: </label>
						<input type="text" name="setting_value[]" value="<?php echo $value; ?>">
					<?php endforeach;?>
					<br />
					<input type="submit" value="Speichern" />
				</form>
			</div>
		</div>
	</div>
	<div id="api_shopware_tabs_attributes">
		<?php include "api/ot_api_attributes_matching.php"; ?>
	</div>
	<div id="api_shopware_tabs_import">
		<div id="api_shopware_import_accordion">
			<h3>Bestellungen</h3>
			<div>
				<form action="<?php echo $ot->get_link("data_source", $data_source->id); ?>" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="action" value="api" />
					<input type="hidden" name="api_action" value="import_orders" />
					<input type="submit" value="Importieren" />
				</form>
			</div>
			
			<h3>Anbieter</h3>
			<div>
				<form action="<?php echo $ot->get_link("data_source", $data_source->id); ?>" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="action" value="api" />
					<input type="hidden" name="api_action" value="import_suppliers" />
					<input type="submit" value="Importieren" />
				</form>
				<?php if ($suppliers = $data_source->suppliers->all()): ?>
					<form action="<?php echo $ot->get_link("data_source", $data_source->id); ?>" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="action" value="api" />
						<input type="hidden" name="api_action" value="save_suppliers" />
						<table>
							<?php foreach ($suppliers as $supplier): ?>
								<tr>
									<td><?php echo $supplier->external_name; ?></td>
									<td><?php echo Supplier::create_dropdown_menu("supplier[" . $supplier->id . "]", 'Wählen...', $supplier->supplier); ?></td>
								</tr>
							<?php endforeach; ?>
						</table>
						<input type="submit" value="Speichern" />
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function() {
		$('#api_shopware_tabs').tabs();
		$('#api_shopware_import_accordion').accordion({ heightStyle: "content" });
		$('#api_shopware_settings_accordion').accordion({ heightStyle: "content" });
	});
</script>

