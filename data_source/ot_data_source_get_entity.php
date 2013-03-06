<?php

$data_source = DataSource::get($_GET["id"]);
$api_name = $data_source->api->name;

?>

<h1><?php echo $data_source->name; ?></h1>

<?php echo $data_source->api->name; 

	include "api/$api_name/main.php";

?>