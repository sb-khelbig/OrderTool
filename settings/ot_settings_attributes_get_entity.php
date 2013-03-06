<?php $attribute = Attribute::get($_GET['id']); ?>

<h1><?php echo $attribute->name; ?></h1>

<form>
	<fieldset>
		<label for="type">Typ: </label>
		<select name="type">
			<option value="0" <?php if ($attribute->type == 0) echo 'selected'; ?>>Textzeile</option>
			<option value="1" <?php if ($attribute->type == 1) echo 'selected'; ?>>Checkbox</option>
			<option value="2" <?php if ($attribute->type == 2) echo 'selected'; ?>>Textfeld</option>
			<option value="3" <?php if ($attribute->type == 3) echo 'selected'; ?>>Zahl</option>
			<option value="4" <?php if ($attribute->type == 4) echo 'selected'; ?>>Datum</option>
			<option value="5" <?php if ($attribute->type == 5) echo 'selected'; ?>>Auswahl</option>
		</select> <br />
	</fieldset>
<?php if ($attribute->type == 5): ?>
	<fieldset>
		<legend>Auswahl</legend>
	<?php foreach ($attribute->choices() as $choice): ?>
		<input type="text" name="select[]" value="<?php echo $choice->data; ?>" /> <br />
	<?php endforeach; ?>
	</fieldset>
<?php endif; ?>
</form>