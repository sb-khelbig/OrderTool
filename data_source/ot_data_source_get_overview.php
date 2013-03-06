<?php

$data_sources = DataSource::all();

foreach ($data_sources as $data_source)
{
	echo "<a href='".$ot->get_link("data_source", $data_source->id)."'>".$data_source->api->name."</a><br />";
}


?>