<?php $attribute = Attribute::get($_GET['id']);

if ($values = isset($_POST['values']) ? $_POST['values'] : FALSE) {
	foreach(Value::filter(array('ref_id' => $attribute->id, 'attribute_id' => $attribute->id)) as $value) {
		if (array_key_exists($value->id, $values)) {
			if ($value->data != $values[$value->id]) {
				$value->data = $values[$value->id];
				$value->update();
			}
		} else {
			//TODO: delete
		}
	}
}

if ($new_values = isset($_POST['new_values']) ? $_POST['new_values'] : FALSE) {
	foreach ($new_values as $data) {
		$value = new Value();
		$value->reference = $attribute->id;
		$value->attribute = $attribute->id;
		$value->data = $data;
		Value::add($value);
	}
	Value::save_new();
}

$redirect = $ot->get_link('products', $attribute->id, 'attributes');
header("Location: $redirect");

