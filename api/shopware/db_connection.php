<?php

$host = $options["api_host"];
$user = $options["api_user"];
$pass = $options["api_pass"];
$db = $options["api_db"];

class MySQL_extern extends MySQL {
	protected static $data;
	protected static $connection;
	protected static $errors = array();
}

MySQL_extern::connect($host, $user, $pass);
MySQL_extern::select_db($db);