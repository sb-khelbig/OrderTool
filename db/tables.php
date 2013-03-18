<?php include __DIR__ . '\fields.php';

abstract class BaseTable {
	
	// Static
	protected static $table_name = '';
	protected static $member = array();
	
	public static function getTableName() {
		return static::$table_name;
	}
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField()
			);
	}
	
	public static function &get($id) {
		if (!array_key_exists($id, static::$member)) {
			$result = get_row_by_id($id, static::getTableName());
			static::$member[$id] = new static($result, $status = 'loaded');
		}
		return static::$member[$id];
	}
	
	public static function filter($conditions = array()) {
		$orders = array();
		
		$query = "	SELECT *
					FROM " . static::getTableName() . "
					WHERE " . create_where_clause($conditions);
		
		if ($result = MySQL::query($query)) {
			while ($row = MySQL::fetch($result)) {
				if (!array_key_exists($row['id'], static::$member)) {
					static::$member[$row['id']] = new static($row, $status = 'loaded');
				}
				$orders[] = &static::$member[$row['id']];
			}
		}
		
		return $orders;
	}
	
	public static function &all($limit = null) {
		$limit = $limit ? "LIMIT $limit" : "";
		
		$query = "	SELECT *
					FROM " . static::getTableName() . "
					$limit";
		
		if ($result = MySQL::query($query)) {
			while ($row = MySQL::fetch($result)) {
				if (!array_key_exists($row['id'], static::$member)) {
					static::$member[$row['id']] = new static($row, $status = 'loaded');
				}
			}
			return static::$member;
		}
		throw new MySQLError();
	}
	
	public static function bulk_save(&$members, $recursion = array()) {
		$field_names = static::getFields();
		
		$fields = array(
				'ForeignKeyField' => array(),
				'BackLinkField' => array(),
				'Insert' => array());
		
		foreach ($field_names as $name => $field) {
			switch(get_class($field)) {
				case 'ForeignKeyField':
					$fields['ForeignKeyField'][$name] = array();
					break;
				case 'BackLinkField':
					if (!in_array($field->getClass(), $recursion)) {
						$fields['BackLinkField'][$name] = array();
					}
					break;
				case 'ReferenceLinkField':
					break;
				case 'AttributeValueField':
					break;
				default:
					$fields['Insert'][$name] = array();
			}
		}
		
		$hash_index = array();
		$saved_members = array();
		foreach ($members as &$member) {
			if ($member->isNew()) {
				$hash = spl_object_hash($member);
				if (!array_key_exists($hash, $hash_index)) {
					$hash_index[$hash] = 0;
					$saved_members[] = $member;
					foreach ($fields as &$names) {
						foreach ($names as $name => &$data) {
							$data[] = $member->$name;
						}
					}
				}
				$hash_index[$hash]++;
			}
		}
		
		if (!$recursion) mysql_query("START TRANSACTION");
		
		$recursion[] = get_called_class();
		
		// save ForeignKeys 
		foreach ($fields['ForeignKeyField'] as $name => &$data) {
			$save = array();
			foreach ($data as $value) {
				if (is_object($value)) $save[] = $value;
			}
			$class = $field_names[$name]->getClass();
			if ($save) $class::bulk_save($save, $recursion);
		}
		
		// save Vars
		$insert = array();
		foreach ($fields['Insert'] as $name => &$data) {
			foreach ($data as $index => $value) {
				$insert[$index][] = "'$value'";
			}
		}
		
		foreach ($fields['ForeignKeyField'] as $name => $data) {
			foreach ($data as $index => $value) {
				if (is_object($value)) {
					$insert[$index][] = "'" . $value->id . "'";
				} else {
					$insert[$index][] = "'$value'";
				}
			}
		}
		
		$fk_field_names = array();
		foreach (array_keys($fields['ForeignKeyField']) as $name) {
			$fk_field_names[] = $field_names[$name]->savable();
		}
		
		foreach ($insert as $index => &$values) {
			$values = "(" . join(', ', $values) . ")";
		}
		
		if ($insert) {
			$query = "	INSERT INTO " . static::getTableName() . "
						(" . join(', ', array_merge(array_keys($fields['Insert']), $fk_field_names)) . ")
					VALUES " . join(', ', $insert);
			
			if ($result = MySQL::query($query)) {
				if (MySQL::affected_rows() == count($insert)) {
					$query = "	SELECT id
								FROM " . static::getTableName() . "
								ORDER BY id DESC
								LIMIT " . count($insert);
					
					if ($result = MySQL::query($query)) {
						$ids = array();
						while ($row = MySQL::fetch($result)) {
							$ids[] = $row['id'];
						}
						
						foreach (array_reverse($ids) as $index => $id) {
							$saved_members[$index]->id = $id;
							$saved_members[$index]->setStatus('saved');
						}
						
						foreach ($fields['BackLinkField'] as $name => &$data) {
							$class = $field_names[$name]->getClass();
							$objs = array();
							foreach ($data as &$obj) {
								$objs = array_merge($objs, $obj->all());
							}
							if ($objs) {
								$class::bulk_save($objs, $recursion);
							}
						}
						
						if (count($recursion) == 1) {
							Value::save_new();
							mysql_query("COMMIT");
						}
					} else {
						throw new MySQLError();
					}
				} else {
					throw new MySQLError('Row mismatch!', 0);
				}
			} else {
				throw new MySQLError();
			}
		}
	}
	
	// NonStatic
	protected $fields;
	protected $status = 'new';
	
	function __construct(array $values = array(), $status = 'new') {
		$this->fields = $this::getFields($this);
		
		foreach ($this->fields as $field) {
			if ($name = $field->savable()) {
				if (array_key_exists($name, $values)) {
					$field->set($values[$name]);
				}
			}
		}
		
		$this->status = $status;
	}
	
	function __get($name) {
		if (array_key_exists($name, $this->fields)) {
			return $this->fields[$name]->get();
		}
		throw new InvalidValueException("Field '$name' in class '" . get_called_class() . "' does not exist!");
	}
	
	function __set($name, $value) {
		if (array_key_exists($name, $this->fields)) {
			$this->fields[$name]->set($value);
			$this->setStatus('changed');
		} else {
			throw new InvalidValueException("Field '$name' in class '" . get_called_class() . "' does not exist!");
		}
	}
	
	function __toString() {
		return (string) $this->id;
	}
	
	public function save($bulk = FALSE) {
		$values = array();
		foreach ($this->fields as $field) {
			if ($name = $field->savable()) {
				if (is_object($value = $field->get())) {
					$value = $value->id;
				}
				$values[$name] = "'$value'";
			}
		}
		if ($bulk) {
			return $values;
		} else {
			$this->id = insert(static::getTableName(), $values);
			$this->setStatus('saved');
		}
	}
	
	public function update() {
		$values = array();
		foreach ($this->fields as $field) {
			if ($name = $field->savable()) {
				if (is_object($value = $field->get())) {
					$value = $value->id;
				}
				$values[] = "`$name` = '$value'";
			}
		}
		
		$query = "	UPDATE " . static::getTableName() . "
					SET " . join(', ', $values) . "
					WHERE id = " . $this->id;
		
		$result = MySQL::query($query);
		
		return MySQL::affected_rows();
	}
	
	public function isNew() {
		return $this->status == 'new';
	}
	
	public function isSaved() {
		return $this->status == 'saved';
	}
	
	public function isLoaded() {
		return $this->status == 'loaded';
	}
	
	public function isChanged() {
		return $this->status == 'changed';
	}
	
	public function setStatus($status) {
		if (!($this->isNew() && ($status == 'changed'))) $this->status = $status;
	}
	
	public function toArray() {
		$data = array();
		foreach (static::getFields() as $name => $field) {
			if ($column = $field->savable()) {
				if (is_object($value = $this->$name)) {
					$value = $value->id;
				}
				$data[$column] = $value;
			}
		}
		return $data;
	}
}

class API extends BaseTable {
	protected static $table_name = 'ot_api';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
		);
	}
}

class Customer extends BaseTable {
	protected static $table_name = 'ot_customer';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'addresses' => new BackLinkField($instance, 'customer_id', 'CustomerAddress', 'customer'),
				'attributes' => new AttributeValueField($instance),
		);
	}

}

class CustomerAddress extends BaseTable {
	protected static $table_name = 'ot_customer_address';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'type' => new BooleanField('type'),
				'customer' => new ForeignKeyField('customer_id', 'Customer'),
				'attributes' => new AttributeValueField($instance),
		);
	}
}

class DataSource extends BaseTable {
	protected static $table_name = 'ot_data_source';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
				'api' => new ForeignKeyField('api_id', 'API'),
				'options' => new BackLinkField($instance, 'data_source_id', 'DataSourceOption', 'data_source'),
				'attributes' => new BackLinkField($instance, 'data_source_id', 'DataSourceHasAttribute', 'data_source'),
		);
	}
	
	public function getAssocArray() {
		$assoc = array();
		foreach ($this->attributes->all() as $attribute) {
			$assoc[$attribute->ref_table][$attribute->field_name] = $attribute->attribute;
		}
		return $assoc;
	}
	
	public function getOptionsArray() {
		$options = array();
		foreach ($this->options->all() as $option) {
			$options[$option->option_name] = $option->option_value;
		}
		return $options;
	}
}

class DataSourceOption extends BaseTable {
	protected static $table_name = 'ot_data_source_option';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'data_source' => new ForeignKeyField('data_source_id', 'DataSource'),
				'option_name' => new CharField('option_name', 50),
				'option_value' => new CharField('option_value', 50),
		);
	}
}

class DataSourceHasAttribute extends BaseTable {
	protected static $table_name = 'ot_data_source_has_attribute';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'data_source' => new ForeignKeyField('data_source_id', 'DataSource'),
				'field_title' => new CharField('field_title', 75),
				'field_name' => new CharField('field_name', 50),
				'ref_table' => new CharField('ref_table', 50),
				'attribute' => new ForeignKeyField('attribute_id', 'Attribute'),
		);
	}
}

class Order extends BaseTable {
	protected static $table_name = 'ot_order';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'customer' => new ForeignKeyField('customer_id', 'Customer'),
				'data_source' => new ForeignKeyField('data_source_id', 'DataSource'),
				'positions' => new BackLinkField($instance, 'order_id', 'Position', 'order'),
				'attributes' => new AttributeValueField($instance),
				'tickets' => new ReferenceLinkField($instance, 'Ticket', 'ref_table', 'ref_id'),
		);
	}
}

class Position extends BaseTable {
	protected static $table_name = 'ot_position';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'order' => new ForeignKeyField('order_id', 'Order'),
				'attributes' => new AttributeValueField($instance),
		);
	}
}

class Ticket extends BaseTable {
	protected static $table_name = 'ot_ticket';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'category' => new ForeignKeyField('ticket_category_id', 'TicketCategory'),
				'status' => new IntegerField('status'),
				'ref_table' => new CharField('ref_table', 50),
				'ref_id' => new ReferenceIDField(),
				'inquirer_title' => new CharField('inquirer_title', 20),
				'inquirer_first_name' => new CharField('inquirer_first_name', 50),
				'inquirer_last_name' => new CharField('inquirer_last_name', 50),
				'inquirer_mail' => new CharField('inquirer_mail', 80),
				'created' => new IntegerField('timestamp_created'),
				'last_response' => new IntegerField('timestamp_last_response'),
		);
	}
}

class TicketCategory extends BaseTable {
	protected static $table_name = 'ot_ticket';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 50),
		);
	}
}

class Attribute extends BaseTable {
	protected static $table_name = 'ot_attribute';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
				'ref_table' => new CharField('ref_table', 50),
				'type' => new IntegerField('type'),
		);
	}
	
	public function choices() {
		if ($this->type == 5) {
			return Value::filter(array('ref_id' => $this->id, 'attribute_id' => 1));
		}
		return array();
	}
}

class AttributeSet extends BaseTable {
	protected static $table_name = 'ot_attribute_set';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
				'attributes' => new BackLinkField($instance, 'attribute_set_id', 'AttributeSetHasAttribute', 'attribute_set'),
		);
	}
}

class AttributeSetHasAttribute extends BaseTable {
	protected static $table_name = 'ot_attribute_set_has_attribute';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'attribute_set' => new ForeignKeyField('attribute_set_id', 'AttributeSet'),
				'attribute' => new ForeignKeyField('attribute_id', 'Attribute'),
		);
	}
}

class Product extends BaseTable {
	protected static $table_name = 'ot_product';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 100),
				'attributes' => new BackLinkField($instance, 'product_id', 'ProductHasAttributeValue', 'product'),
				'variants' => new BackLinkField($instance, 'product_id', 'ProductVariant', 'product'),
		);
	}
}

class ProductHasAttributeValue extends BaseTable {
	protected static $table_name = 'ot_product_has_attribute_value';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'product' => new ForeignKeyField('product_id', 'Product'),
				'value' => new ForeignKeyField('value_id', 'Value'),
		);
	}
}

class ProductVariant extends BaseTable {
	protected static $table_name = 'ot_product_variant';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'product' => new ForeignKeyField('product_id', 'Product'),
				'pavs' => new BackLinkField($instance, 'product_variant_id', 'ProductVariantHasPAV', 'variant'),
		);
	}
}

class ProductVariantHasPAV extends BaseTable {
	protected static $table_name = 'ot_product_variant_has_pav';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'variant' => new ForeignKeyField('product_variant_id', 'ProductVariant'),
				'pav' => new ForeignKeyField('pav_id', 'ProductHasAttributeValue'),
		);
	}
}

class Supplier extends BaseTable {
	protected static $table_name = 'ot_supplier';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 100),
		);
	}
}

class Offer extends BaseTable {
	protected static $table_name = 'ot_product_offer';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'product' => new ForeignKeyField('product_id', 'Product'),
				'supplier' => new ForeignKeyField('supplier_id', 'Supplier'),
				'matching' => new BackLinkField($instance, 'offer_id', 'OfferHasDataSource', 'offer'),
				'variants' => new BackLinkField($instance, 'offer_id', 'OfferHasVariant', 'offer'),
		);
	}
}

class OfferHasDataSource extends BaseTable {
	protected static $table_name = 'ot_product_offer_has_data_source';
	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'offer' => new ForeignKeyField('offer_id', 'Offer'),
				'data_source' => new ForeignKeyField('data_source_id', 'DataSource'),
				'external_id' => new CharField('external_id', 50),
		);
	}
}

class OfferHasVariant extends BaseTable {
	protected static $table_name = 'ot_product_offer_has_variant';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'offer' => new ForeignKeyField('offer_id', 'Offer'),
				'variant' => new ForeignKeyField('variant_id', 'ProductVariant'),
		);
	}
}

class VoucherList extends BaseTable {
	protected static $table_name = 'ot_voucher_list';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 100),
				'codes' => new BackLinkField($instance, 'voucher_list_id', 'VoucherCode', 'voucher_list'),
		);
	}
}

class VoucherCode extends BaseTable {
	protected static $table_name = 'ot_voucher_code';
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'voucher_list' => new ForeignKeyField('voucher_list_id', 'VoucherList'),
				'code' => new CharField('code', 50),
				'position' => new ForeignKeyField('position_id', 'Position'),
		);
	}
}

class Value extends BaseTable {
	protected static $table_name = 'ot_value';
	protected static $member = array();
	
	private static $new = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'reference' => new ReferenceIDField(),
				'attribute' => new ForeignKeyField('attribute_id', 'Attribute'),
				'data' => new CharField('data', 200),
		);
	}
	
	public static function save_new() {
		
		$insert = array();
		foreach (static::$new as $value) {
			$insert[] = $value->save(TRUE);
		}
		
		if ($insert) {
			
			$fields = array();
			foreach (static::getFields() as $field) {
				if ($name = $field->savable()) $fields[] = $name;
			}
			
			$values = array();
			foreach ($insert as $data) {
				$tmp = array();
				foreach ($fields as $name) {
					$tmp[] = $data[$name];
				}
				$values[] = "(" . join(', ', $tmp) . ")";
			}
		
			$query = "	INSERT INTO ot_value
							(" . join(', ', $fields) . ")
						VALUES " . join(', ', $values);
			
			$result = MySQL::query($query);
		}
	}
	
	public static function add(&$member) {
		if (is_a($member, 'Value')) {
			static::$new[] = $member;
		} else {
			throw new InvalidValueException();
		}
	}
	
	public function html($class = '') {
		$id = $this->id;
		$class = ($class) ? "class=\"$class\"" : '';
		$value = $this->data;
		switch ($this->attribute->type) {
			case 0:
				return "<input $class type=\"text\" name=\"attributes[$id]\" value=\"$value\" />";
			case 1:
				$checked = ($value) ? 'checked="checked"' : '';
				return "<input $class type=\"checkbox\" name=\"attributes[$id]\" value=\"1\" $checked />";
			case 2:
				return "<textarea $class name=\"attributes[$id]\" >$value</textarea>";
			case 3:
				return "<input $class type=\"number\" name=\"attributes[$id]\" value=\"$value\" />";
			case 4:
				return "<input $class type=\"datetime\" name=\"attributes[$id]\" value=\"$value\" />";
			case 5:
				$options = array();
				foreach ($this->attribute->choices() as $value) {
					$selected = ($value->id == $this->data) ? 'selected="selected"' : '';
					$options[] = '<option value="' . $value->id . "\" $selected>" . $value->data . '</option>';
				}
				return "<select $class name=\"attributes[$id]\">$options</select>";
		}
	}
}

class Table {
	private static $members = array(
			'ot_order' => 'Bestellungen',
			'ot_position' => 'Positionen',
			'ot_customer' => 'Kunden',
			'ot_customer_address' => 'Adressen',
		);
	
	public static function get($name) {
		if (!array_key_exists($name, static::$members)) {
			return static::$members[$name];
		}
		return FALSE;
	}
	
	public static function all() {
		return static::$members;
	}
}
