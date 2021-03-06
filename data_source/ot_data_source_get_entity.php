<?php $data_source = DataSource::get($_GET["id"]); ?>

<h1><?php echo $data_source->name; ?></h1>

<fieldset>
	<legend>
		<select name="api">
			<?php foreach (API::all() as $api): ?>
				<?php $selected = ($api === $data_source->api) ? 'selected' : ''; ?>
				<option value="<?php echo $api->id; ?>" <?php echo $selected; ?>><?php echo $api->name; ?></option>
			<?php endforeach; ?>
		</select>
	</legend>
	
	<div class="api-main"><?php include 'api/' . $data_source->api->name . '/main.php'; ?></div>
</fieldset>