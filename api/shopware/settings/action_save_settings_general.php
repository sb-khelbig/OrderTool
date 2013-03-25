<?php $data_source = DataSource::get($_GET['id']);

$setting_names = $_POST["setting_name"];
$setting_values = $_POST["setting_value"];

for ($i = 0; $i < count($setting_names); $i++)
{
	$query = "UPDATE ot_data_source_option
			SET option_value = '$setting_values[$i]'
			WHERE data_source_id = '$data_source->id'
			AND   option_name = '$setting_names[$i]'
			";
	echo $query;
	MySQL::query($query);
}

$redirect = $ot->get_link('data_source', $data_source->id);
header("Location: $redirect");