<?php

class InvalidValueLengthException extends Exception {}

class InvalidValueException extends Exception {}

abstract class BaseField {
	protected $value;
	protected $data;
	
	abstract protected function verify($value);
	
	public function get() {
		return $this->value;
	}
	
	public function set($value) {
		if ($this->verify($value)) {
			$this->value = $value;
		}
	}
	
	public function savable() {
		if (array_key_exists('column_name', $this->data)) {
			return $this->data['column_name'];
		}
		return FALSE;
	}
	
	function __toString() {
		return (string) $this->get();
	}
}

class BooleanField extends BaseField {
	
	function __construct($column_name, $default = 0) {
		$this->set($default);
		$this->data['default'] = $default;
		$this->data['column_name'] = $column_name;
	}
	
	protected function verify($value) {
		return TRUE;
	}
	
	public function set($value) {
		if ($this->verify($value)) {
			$this->value = ($value) ? 1 : 0;
		}
	}
}

class CharField extends BaseField {
	
	function __construct($column_name, $max_length, $default = '') {
		$this->set($default);
		$this->data['default'] = $default;
		$this->data['column_name'] = $column_name;
		$this->data['max_length'] = $max_length;
	}
	
	protected function verify($value) {
		if ($value == NULL) {
			$this->value = '';
			return FALSE;
		} elseif (is_string($value)) {
			if (strlen($value) <= $this->data['max_length']) {
				return TRUE;
			} else {
				throw new InvalidValueLengthException();
			}
		}
		throw new InvalidValueException("ERROR: no string: $value (Column-name: ".$this->data['column_name'].")");
	}
}

class IntegerField extends BaseField {
	
	function __construct($column_name, $default = 0) {
		$this->set($default);
		$this->data['default'] = $default;
		$this->data['column_name'] = $column_name;
	}
	
	protected function verify($value) {
		if (is_numeric($value)) {
			return TRUE;
		}
		throw new InvalidValueException();
	}
	
}

class TextField extends BaseField {

	function __construct($column_name, $default = '') {
		$this->set($default);
		$this->data['default'] = $default;
		$this->data['column_name'] = $column_name;
	}

	protected function verify($value) {
		if (is_string($value)) {
			return TRUE;
		}
		throw new InvalidValueException();
	}

}

class PrimaryKeyField extends BaseField {
	
	function __construct($default = NULL) {
		$this->set($default);
		$this->data['default'] = $default;
		$this->data['column_name'] = 'id';
	}
	
	protected function verify($value) {
		if (is_numeric($value)) {
			return TRUE;
		} elseif (($value == 'NULL') || ($value == NULL)) {
			return TRUE;
		}
		throw new InvalidValueException();
	}
	
	public function get() {
		if ($this->data['default'] == NULL) {
			if (($this->value == NULL) || ($this->value == 0)) {
				return 'NULL';
			}
		} else {
			if (($this->value == 'NULL') || ($this->value == NULL)) {
				return 0;
			}
		}
		return $this->value;
	}
}

class ForeignKeyField extends BaseField {

	function __construct($column_name, $class_name, $default = 0) {
		$this->set($default);
		$this->data['default'] = $default;
		$this->data['column_name'] = $column_name;
		$this->data['class_name'] = $class_name;
	}

	protected function verify($value) {
		if (is_object($value)) {
			if (is_a($value, $this->data['class_name'])) {
				return TRUE;
			}
		} elseif (is_numeric($value)) {
			return TRUE;
		} elseif (($value == 'NULL') || ($value == NULL)) {
			return TRUE;
		}
		throw new InvalidValueException();
	}
	
	public function get() {
		if (is_object($this->value)) {
			return $this->value;
		} elseif (is_numeric($this->value)) {
			if ($this->value > 0) {
				$class = $this->getClass();
				return $this->value = $class::get($this->value);
			}
		} elseif ($this->value == NULL || ($this->value == 'NULL')) {
			if ($this->data['default'] == NULL) {
				return 'NULL';
			}
		}
		return 0;
	}
	
	public function getClass() {
		return $this->data['class_name'];
	}
}

class ReferenceIDField extends BaseField {
	
	function __construct() {
		$this->data['column_name'] = 'ref_id';
	}
	
	protected function verify($value) {
		if (is_object($value)) {
			return TRUE;
		} elseif (is_numeric($value)) {
			return TRUE;
		}
		throw new InvalidValueException();
	}
	
	public function get() {
		if (is_object($this->value)) {
			return $this->value->id;
		}
		return $this->value;
	}
}

class ReferenceLinkField extends BaseField {
	protected $members;
	
	function __construct(&$owner, $linked_class, $table_field, $id_field) {
		$this->data['owner'] = $owner;
		$this->data['class_name'] = $linked_class;
		$this->data['table_field'] = $table_field;
		$this->data['id_field'] = $id_field;
	}
	
	public function savable() {
		return FALSE;
	}
	
	protected function verify($value) {
		if (is_a($value, $this->data['class_name'])) {
			return TRUE;
		}
		throw new InvalidValueException();
	}
	
	public function add(&$member) {
		if ($this->verify($member)) {
			$class = get_class($this->data['owner']);
			$table = $this->data['table_field'];
			$id = $this->data['id_field'] = $id_field;
			$member->$table = $class::getTableName();
			$member->$id = $this->data['owner'];
			$this->members[] = $member;
			$this->data['owner']->setStatus('changed');
		}
	}
	
	public function get() {
		return $this;
	}
	
	public function set($value) {
		$this->add($value);
	}
	
	public function &all() {
		if ($this->data['owner']->isLoaded()) {
			$class = $this->data['class_name'];
			$owner_class = get_class($this->data['owner']);
			$this->members = $class::filter(array($this->data['table_field'] => $owner_class::getTableName(), $this->data['id_field'] => $this->data['owner']->id));
		}
		return $this->members;
	}
	
	public function getClass() {
		return $this->data['class_name'];
	}
}

class BackLinkField extends BaseField {
	protected $members;
	
	function __construct(&$owner, $owner_field_name, $linked_class, $linked_field_name) {
		$this->data['owner'] = $owner;
		$this->data['owner_field_name'] = $owner_field_name;
		$this->data['class_name'] = $linked_class;
		$this->data['field_name'] = $linked_field_name;
	}
	
	public function savable() {
		return FALSE;
	}
	
	protected function verify($value) {
		if (is_a($value, $this->data['class_name'])) {
			return TRUE;
		}
		throw new InvalidValueException();
	}
	
	public function add(&$member) {
		if ($this->verify($member)) {
			$fn = $this->data['field_name'];
			$member->$fn = $this->data['owner'];
			$this->members[] = $member;
			$this->data['owner']->setStatus('changed');
		}
	}
	
	public function get() {
		return $this;
	}
	
	public function set($value) {
		$this->add($value);
	}
	
	public function &all() {
		if ($this->data['owner']->isLoaded()) {
			$class = $this->data['class_name'];
			$this->members = $class::filter(array($this->data['owner_field_name'] => $this->data['owner']->id));
		}
		return $this->members;
	}
	
	public function getClass() {
		return $this->data['class_name'];
	}
}

class ManyToManyField extends BaseField {
	protected $members;
	
	function __construct($owner, $linked_class, $table_name, $owner_field_name, $linked_class_field_name) {
		$this->data['owner'] = $owner;
		$this->data['owner_field_name'] = $owner_field_name;
		$this->data['table_name'] = $table_name;
		$this->data['class_name'] = $linked_class;
		$this->data['field_name'] = $linked_class_field_name;
	}
	
	protected function verify($value) {
		if (is_object($value)) {
			if (is_a($value, $this->data['class_name'])) {
				return TURE;
			}
		} elseif (is_numeric($var)) {
			return TRUE;
		}
		return FALSE;
	}
	
	public function get() {
		return $this;
	}
	
	public function add($member) {
		return FALSE;
	}
	
	public function &all() {
		if ($this->data['owner']->isLoaded()) {
			$class = $this->data['class_name'];
			
			$result = select(
					$this->data['table_name'],
					array($this->data['owner_field_name'] => $this->data['owner']->id),
					$this->data['field_name']);
			
			$ids = array();
			while ($row = MySQL::fetch($result)) {
				$ids[] = $row[$this->data['field_name']];
			}
			
			$this->members = ($ids) ? $class::filter(array('id' => $ids)) : array();
		}
		return $this->members;
	}
}

class AttributeValueField extends BaseField {
	private $members;
	
	function __construct(&$owner) {
		$this->data['owner'] = $owner;
	}
	
	public function savable() {
		return FALSE;
	}
	
	protected function verify($value) {
		if (is_a($value, 'Value')) {
			return TRUE;
		}
		throw new InvalidValueException();
	}
	
	public function get() {
		return $this;
	}
	
	public function set($value) {
		if ($this->verify($value)) {
			$this->members[] = $value;
			Value::add($value);
		}
	}
	
	public function add($attribute, $data) {
		$value = new Value();
		$value->reference = $this->data['owner'];
		$value->attribute = $attribute;
		$value->data = $data;
		$this->set($value);
	}
	
	public function &all() {
		if ($this->data['owner']->isLoaded()) {
			$class = get_class($this->data['owner']);
			$attributes = Attribute::filter(array('ref_table' => $class::getTableName()));
			$ids = array();
			foreach ($attributes as $atr) {
				$ids[] = $atr->id;
			}
			if ($ids) {
				$this->members = Value::filter(array('ref_id' =>$this->data['owner']->id, 'attribute_id' => $ids));
			} else {
				$this->members = array();
			}
		}
		return $this->members;
	}
	
	
}
