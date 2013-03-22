<?php $data_source = DataSource::get($_GET['id']);

include __DIR__ . '/../db_connection.php';

$query = "	SELECT id, name
			FROM s_articles_supplier";

$suppliers_extern = array();
if ($result = MySQL_extern::query($query)) {
	while ($row = MySQL_extern::fetch($result)) {
		$suppliers_extern[$row['id']] = $row['name'];
	}
}

var_dump($suppliers_extern);
