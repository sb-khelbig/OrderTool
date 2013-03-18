<?php include '../db/mysql.php'; include '../db/tables.php';

function build_variants($value, $variant, &$variants) {
	if ($vars = array_pop($value)) {
		foreach ($vars as $index => $var) {
			$current = $variant;
			$current[] = $var;
			build_variants($value, $current, $variants);
		}
	} else {
		$variants[] = $variant;
	}
}

$json = array('error' => TRUE, 'msg' => 'Unknown error', 'data' => array());

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
	case 'attribute':
		if (isset($_GET['id'])) {
			if ($id = $_GET['id']) {
				try {
					$attribute = Attribute::get($id);
					$json['data']['attribute'] = array('id' => (int) $id, 'name' => $attribute->name);
					$values = Value::filter(array('ref_id' => $attribute->id, 'attribute_id' => $attribute->id));
					foreach ($values as $value) {
						$json['data']['values'][$value->id] = $value->data;
					}
					$json['error'] = FALSE;
				} catch (Exception $e) {
					$json['msg'] = "$e";
				}
			} else {
				$json['msg'] = 'ID is 0';
			}
		} else {
			$json['msg'] = 'ID not set';
		}
		break;
	case 'variants':
		if (isset($_GET['id'])) {
			if ($id = $_GET['id']) {
				try {
						$product = Product::get($id);
						$pavs = $product->attributes->all();
						$attributes = array();
						foreach ($pavs as $pav) {
							$attributes[$pav->value->attribute->id][$pav->value->id] = $pav->value->data;
						}
						$variants = array();
						build_variants($attributes, array(), $json['data']);
					$json['error'] = FALSE;
				} catch (Exception $e) {
					$json['msg'] = "$e";
				}
			} else {
				$json['msg'] = 'ID is 0';
			}
		} else {
			$json['msg'] = 'ID not set';
		}
		break;
	default:
		$json['msg'] = "Unknown action '$action'";
}

echo json_encode($json);