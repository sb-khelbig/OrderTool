<?php

$values = array(
		'name' => isset($_POST['name']) ? mysql_real_escape_string($_POST['name']) : die("name is missing!"),
		'ref_table' => isset($_POST['ref_table']) ? mysql_real_escape_string($_POST['ref_table']) : die("ref_table is missing!"),
		'type' => isset($_POST['type']) ? mysql_real_escape_string($_POST['type']) : die("type is missing!"),
		'select' => isset($_POST['select']) ? $_POST['select'] : array(),
	);

mysql_query("START TRANSACTION");

$attribute = new Attribute($values);
$attribute->save();

if ($values['type'] == 5) {
	foreach ($values['select'] as $select) {
		$value = new Value(array(
				'ref_id' => $attribute,
				'attribute_id' => 1,
				'data' => mysql_real_escape_string($select)));
		Value::add($value);
	}
	Value::save_new();
}

mysql_query("COMMIT");

$redirect = $ot->get_link('settings', $attribute->id, 'attributes');
header("Location: $redirect");

