<?php include __DIR__ . '\fields.php';

abstract class BaseTable {
	
	// Static
	protected static $data = array(
			'table' => '',
			'title' => '',
			'title_plural' => '',
		);
	
	protected static $member = array();
	
	public static function getTableName() {
		return static::$data['table'];
	}
	
	public static function getTitle($plural = TRUE) {
		if ($plural) {
			return static::$data['title_plural'];
		}
		return static::$data['title'];
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
	
	public static function &all($limit = null, $renew = FALSE) {
		if (isset(static::$data['all_query_done']) && !$renew) {
			return static::$member;
		}
		
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
			static::$data['all_query_done'] = True;
			return static::$member;
		}
		throw new MySQLError();
	}
	
	public static function bulk_save(&$members, $recursion = array()) {
		$start = !$recursion;
		
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
						$recursion[] = $field->getClass();
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
		
		if ($start) MySQL::start_transaction();
		
		$recursion[] = get_called_class();
		
		// save ForeignKeys 
		foreach ($fields['ForeignKeyField'] as $name => &$data) {
			if (!in_array($field_names[$name]->getClass(), $recursion)) {
				$save = array();
				foreach ($data as $value) {
					if (is_object($value)) $save[] = $value;
				}
				$class = $field_names[$name]->getClass();
				if ($save) $class::bulk_save($save, $recursion);
			}
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
		foreach (array_keys($fields['Insert']) as $name) {
			$fk_field_names[] = MySQL::quote($field_names[$name]->savable());
		}
		
		foreach (array_keys($fields['ForeignKeyField']) as $name) {
			$fk_field_names[] = MySQL::quote($field_names[$name]->savable());
		}
		
		foreach ($insert as $index => &$values) {
			$values = "(" . join(', ', $values) . ")";
		}
		
		if ($insert) {
			$query = "	INSERT INTO " . static::getTableName() . "
						(" . join(', ', $fk_field_names) . ")
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
						
						if ($start) {
							Value::save_new();
							MySQL::commit();
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
	
	public static function create_dropdown_menu($name = '', $initial = '', $selected = 0, $class = '', $conditions = array()) {
		
		if ($conditions) {
			$objects = static::filter($conditions);
		} else {
			$objects = static::all();
		}
		
		$name = ($name) ? $name : static::getTableName();
		$class = ($class) ? "class=\"$class\"" : '';
		
		$select[] = "<select name=\"$name\" $class>";
		
		if ($initial) {
			$select[] = '<option value="0">';
			$select[] = $initial;
			$select[] = '</option>';
		}
		
		if (is_object($selected)) {
			$selected = $selected->id;
		}
		
		foreach ($objects as $obj) {
			$sel = ($selected == $obj->id) ? 'selected="selected"' : '';
			$select[] = '<option value="' . $obj->id . "\" $sel>$obj</option>";
		}
		
		$select[] = "</select>";
		
		return join('', $select);
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

class User extends BaseTable {
	protected static $data = array(
		'table' => 'ot_user',
		'title' => 'Benutzer',
		'title_plural' => 'Benutzer',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'title' => new IntegerField('title'),
				'first_name' => new CharField('first_name', 50),
				'last_name' => new CharField('last_name', 50),
				'mail' => new CharField('mail', 75),
				'password' => new CharField('password', 64),
		);
	}
	
	public function title($name = '', $class = '') {
		if (!$name) {
			return $this->titles[$this->title];
		}
	
		$class = ($class) ? "class=\"$class\"" : '';
	
		$select[] = "<select name=\"$name\" $class>";
		foreach ($this->titles as $code => $title) {
			$selected = ($code == $this->title) ? 'selected="selected"' : '';
			$select[] = "<option value=\"$code\" $selected>$title</option>";
		}
		$select[] = "</select>";
	
		return join('', $select);
	}
	
	function __toString() {
		return (string) $this->first_name . ' ' . $this->last_name;
	}
}

class API extends BaseTable {
	protected static $data = array(
			'table' => 'ot_api',
			'title' => '',
			'title_plural' => '',
	);

	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
		);
	}
	
	function __toString() {
		return (string) $this->name;
	}
}

class Customer extends BaseTable {
	protected static $data = array(
			'table' => 'ot_customer',
			'title' => 'Kunde',
			'title_plural' => 'Kunden',
	);

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
	protected static $data = array(
			'table' => 'ot_customer_address',
			'title' => 'Adresse',
			'title_plural' => 'Adressen',
	);

	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'type' => new BooleanField('type'),
				'customer' => new ForeignKeyField('customer_id', 'Customer'),
				'order' => new ForeignKeyField('order_id', 'Order'),
				'attributes' => new AttributeValueField($instance),
		);
	}
}

class Contact extends BaseTable {
	protected static $data = array(
			'table' => 'ot_contact',
			'title' => 'Kontakt',
			'title_plural' => 'Kontakte',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'title' => new IntegerField('title'),
				'first_name' => new CharField('first_name', 50),
				'last_name' => new CharField('last_name', 50),
				'mail' => new CharField('mail', 75),
		);
	}
	
	protected $titles = array('Unbekannt', 'Herr', 'Frau');
	
	public function title($name = '', $class = '') {
		if (!$name) {
			return $this->titles[$this->title];
		}
	
		$class = ($class) ? "class=\"$class\"" : '';
	
		$select[] = "<select name=\"$name\" $class>";
		foreach ($this->titles as $code => $title) {
			$selected = ($code == $this->title) ? 'selected="selected"' : '';
			$select[] = "<option value=\"$code\" $selected>$title</option>";
		}
		$select[] = "</select>";
	
		return join('', $select);
	}
	
	function __toString() {
		return (string) $this->first_name . ' ' . $this->last_name;
	}
}

class DataSource extends BaseTable {
	protected static $data = array(
			'table' => 'ot_data_source',
			'title' => '',
			'title_plural' => '',
	);

	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
				'api' => new ForeignKeyField('api_id', 'API'),
				'options' => new BackLinkField($instance, 'data_source_id', 'DataSourceOption', 'data_source'),
				'attributes' => new BackLinkField($instance, 'data_source_id', 'DataSourceHasAttribute', 'data_source'),
				'suppliers' => new BackLinkField($instance, 'data_source_id', 'SupplierHasDataSource', 'data_source'),
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
	
	function __toString() {
		return (string) $this->name;
	}
}

class DataSourceOption extends BaseTable {
	protected static $data = array(
			'table' => 'ot_data_source_option',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_data_source_has_attribute',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_order',
			'title' => 'Bestellung',
			'title_plural' => 'Bestellungen',
	);

	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'customer' => new ForeignKeyField('customer_id', 'Customer'),
				'data_source' => new ForeignKeyField('data_source_id', 'DataSource'),
				'positions' => new BackLinkField($instance, 'order_id', 'Position', 'order'),
				'attributes' => new AttributeValueField($instance),
				'addresses' => new BackLinkField($instance, 'order_id', 'CustomerAddress', 'order'),
				//'tickets' => new ReferenceLinkField($instance, 'Ticket', 'ref_table', 'ref_id'),
		);
	}
}

class Position extends BaseTable {
	protected static $data = array(
			'table' => 'ot_position',
			'title' => 'Position',
			'title_plural' => 'Positionen',
	);

	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'order' => new ForeignKeyField('order_id', 'Order'),
				'attributes' => new AttributeValueField($instance),
		);
	}
}

class MailTemplate extends BaseTable {
	protected static $data = array(
			'table' => 'ot_mail_template',
			'title' => 'Template',
			'title_plural' => 'Templates',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 100),
				'text' => new TextField('text'),
		);
	}
	
	function __toString() {
		return (string) $this->name;
	}
}

class Ticket extends BaseTable {
	protected static $data = array(
			'table' => 'ot_ticket',
			'title' => 'Ticket',
			'title_plural' => 'Tickets',
	);

	protected static $member = array();

	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'category' => new ForeignKeyField('ticket_category_id', 'TicketCategory'),
				'status' => new IntegerField('status'),
				'ref_table' => new CharField('ref_table', 50),
				'created' => new IntegerField('timestamp_created'),
				'participants' => new BackLinkField($instance, 'ticket_id', 'TicketParticipant', 'ticket'),
				'entries' => new BackLinkField($instance, 'ticket_id', 'TicketEntry', 'ticket'),
				'references' => new BackLinkField($instance, 'ticket_id', 'TicketReference', 'ticket'),
		);
	}
	
	public function last_edited() {
		$query = "	SELECT timestamp_created
					FROM ot_ticket_entry
					WHERE ticket_id = " . $this->id . "
					ORDER BY timestamp_created DESC";
		
		$result = MySQL::query($query);
		
		if ($timestamp = MySQL::fetch($result)) {
			return (int) $timestamp['timestamp_created'];
		}
		
		return 0;
	}
	
	public function entry_count() {
		$query = "	SELECT COUNT(*) AS count
					FROM ot_ticket_entry
					WHERE ticket_id = " . $this->id;
		
		$result = MySQL::query($query);
		
		if ($count = MySQL::fetch($result)) {
			return (int) $count['count'];
		}
	}
}

class TicketParticipant extends BaseTable {
	protected static $data = array(
			'table' => 'ot_ticket_participant',
			'title' => 'Teilnehmer',
			'title_plural' => 'Teilnehmer',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'ticket' => new ForeignKeyField('ticket_id', 'Ticket'),
				'type' => new IntegerField('type'),
				'token' => new CharField('token', 64),
				'title' => new IntegerField('title'),
				'first_name' => new CharField('first_name', 50),
				'last_name' => new CharField('last_name', 50),
				'mail' => new CharField('mail', 75),
		);
	}
	
	protected $titles = array('Unbekannt', 'Herr', 'Frau');
	
	protected $types = array('Mitarbeiter', 'Kunde', 'Extern');
	
	public function title($name = '', $class = '') {
		if (!$name) {
			return $this->titles[$this->title];
		}
		
		$class = ($class) ? "class=\"$class\"" : '';
		
		$select[] = "<select name=\"$name\" $class>";
		foreach ($this->titles as $code => $title) {
			$selected = ($code == $this->title) ? 'selected="selected"' : '';
			$select[] = "<option value=\"$code\" $selected>$title</option>";
		}
		$select[] = "</select>";
		
		return join('', $select);
	}

	public function type($name = '', $class = '') {
		if (!$name) {
			return $this->types[$this->type];
		}
		
		$class = ($class) ? "class=\"$class\"" : '';
		
		$select[] = "<select name=\"$name\" $class>";
		foreach ($this->types as $code => $type) {
			$selected = ($code == $this->type) ? 'selected="selected"' : '';
			$select[] = "<option value=\"$code\" $selected>$type</option>";
		}
		$select[] = "</select>";
		
		return join('', $select);
	}
}

class TicketReference extends BaseTable {
	protected static $data = array(
			'table' => 'ot_ticket_reference',
			'title' => '',
			'title_plural' => '',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'ticket' => new ForeignKeyField('ticket_id', 'Ticket'),
				'ref_id' => new ReferenceIDField(),
		);
	}
}

class TicketCategory extends BaseTable {
	protected static $data = array(
			'table' => 'ot_ticket_category',
			'title' => '',
			'title_plural' => '',
	);

	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 50),
		);
	}
	
	function __toString() {
		return (string) $this->name;
	}
}

class TicketEntry extends BaseTable {
	protected static $data = array(
			'table' => 'ot_ticket_entry',
			'title' => 'Korrespondenz',
			'title_plural' => 'Korrespondenzen',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'ticket' => new ForeignKeyField('ticket_id', 'Ticket'),
				'participant' => new ForeignKeyField('participant_id', 'TicketParticipant'),
				'created' => new IntegerField('timestamp_created'),
				'text' => new TextField('text'),
				'rights' => new BackLinkField($instance, 'entry_id', 'TicketEntryRight', 'entry')
		);
	}
}

class TicketEntryRight extends BaseTable {
	protected static $data = array(
			'table' => 'ot_ticket_entry_right',
			'title' => 'Recht',
			'title_plural' => 'Rechte',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'entry' => new ForeignKeyField('entry_id', 'TicketEntry'),
				'participant' => new ForeignKeyField('participant_id', 'TicketParticipant'),
				'read' => new BooleanField('read'),
		);
	}
}

class Attribute extends BaseTable {
	protected static $data = array(
			'table' => 'ot_attribute',
			'title' => '',
			'title_plural' => '',
	);

	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
				'short_name' => new CharField('short_name', 20),
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
	
	function __toString() {
		return (string) $this->name;
	}
}

class AttributeSet extends BaseTable {
	protected static $data = array(
			'table' => 'ot_attribute_set',
			'title' => '',
			'title_plural' => '',
	);

	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 75),
				'attributes' => new BackLinkField($instance, 'attribute_set_id', 'AttributeSetHasAttribute', 'attribute_set'),
		);
	}
	
	function __toString() {
		return (string) $this->name;
	}
}

class AttributeSetHasAttribute extends BaseTable {
	protected static $data = array(
			'table' => 'ot_attribute_set_has_attribute',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_product',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_product_has_attribute_value',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_product_variant',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_product_variant_has_pav',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_supplier',
			'title' => '',
			'title_plural' => '',
	);

	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'name' => new CharField('name', 100),
				'contacts' => new ManyToManyField($instance, 'Contact', 'ot_supplier_has_contact', 'supplier_id', 'contact_id'),
		);
	}
	
	function __toString() {
		return (string) $this->name;
	}
}

class SupplierHasContact extends BaseTable {
	protected static $data = array(
			'table' => 'ot_supplier_has_contact',
			'title' => '',
			'title_plural' => '',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'supplier' => new ForeignKeyField('supplier_id', 'Supplier'),
				'contact' => new ForeignKeyField('contact_id', 'Contact'),
		);
	}
}

class SupplierHasDataSource extends BaseTable {
	protected static $data = array(
			'table' => 'ot_supplier_has_data_source',
			'title' => '',
			'title_plural' => '',
	);
	
	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'supplier' => new ForeignKeyField('supplier_id', 'Supplier'),
				'data_source' => new ForeignKeyField('data_source_id', 'DataSource'),
				'external_name' => new CharField('external_name', 200),
				'external_id' => new CharField('external_id', 50),
		);
	}
}

class Offer extends BaseTable {
	protected static $data = array(
			'table' => 'ot_product_offer',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_product_offer_has_data_source',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_product_offer_has_variant',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_voucher_list',
			'title' => '',
			'title_plural' => '',
	);

	protected static $member = array();
	
	protected static function getFields($instance = null) {
		return array(
				'id' => new PrimaryKeyField(),
				'data_source' => new ForeignKeyField('data_source_id', 'DataSource'),
				'name' => new CharField('name', 100),
				'codes' => new BackLinkField($instance, 'voucher_list_id', 'VoucherCode', 'voucher_list'),
		);
	}
}

class VoucherCode extends BaseTable {
	protected static $data = array(
			'table' => 'ot_voucher_code',
			'title' => '',
			'title_plural' => '',
	);

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
	protected static $data = array(
			'table' => 'ot_value',
			'title' => '',
			'title_plural' => '',
	);

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
			'ot_order' => 'Order',
			'ot_position' => 'Position',
			'ot_customer' => 'Customer',
			'ot_customer_address' => 'CustomerAddress',
			'ot_mail_template' => 'MailTemplate',
		);
	
	public static function get($table) {
		if (array_key_exists($table, static::$members)) {
			return static::$members[$table];
		}
		return FALSE;
	}
	
	public static function all() {
		return static::$members;
	}
}
