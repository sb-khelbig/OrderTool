<pre>
<?php
class A {
	protected static $data = 'A';
	
	public static function &getData() {
		return static::$data;
	}
}

class B extends A {
	protected static $data = 'B';
}

echo B::getData();

?>
</pre>