<?php 

class MySQL_extern extends MySQL {
	protected static $data;
	protected static $connection;
	protected static $errors = array();
}

MySQL_extern::connect('85.214.202.153', 'k.helbig', '124578aa');
MySQL_extern::select_db('shopware');