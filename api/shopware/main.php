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
		<p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
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
	});
</script>

