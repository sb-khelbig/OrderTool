<?php

	$api_action = $_POST["api_action"];
	
	switch ($api_action)
	{
		case "import_orders":
			include __DIR__."/import/import_orders.php";
		break;
		default:
			echo "ERROR: NO API_ACTION DEFINED!";
		break;
	}

?>