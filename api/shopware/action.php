<?php

	$api_action = $_POST["api_action"];
	
	switch ($api_action)
	{
		case "import_orders":
			include __DIR__."/import/import_orders.php";
			break;
		case 'import_suppliers':
			include __DIR__."/import/import_suppliers.php";
			break;
		case 'save_suppliers':
			include __DIR__."/action_save_suppliers.php";
			break;
		default:
			echo "ERROR: NO API_ACTION DEFINED!";
			break;
	}

?>