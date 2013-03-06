<?php
$name = (isset($_POST['name'])) ? $_POST['name'] : die("Kein Name angegeben!");

$result = mysql_query("	INSERT INTO ot_attribute_set
							(name)
						VALUES ('$name')") or die('MYSQLError: ' . mysql_error());

$id = mysql_insert_id();

$redirect = $ot->get_link('settings', $id, 'attributeset');
header("Location: $redirect");