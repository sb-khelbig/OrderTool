<?php
	echo "<pre>";
	$data_source = DataSource::get($_GET["id"]);
	
	$referer = $_SERVER["HTTP_REFERER"];
	
	$starttime = round(microtime(true),4);
	
	//// FETCH ATTRIBUTE_ASSIGNMENTS
	$attr_assoc = $data_source->getAssocArray();
	
	//// FETCH OPTIONS
	// TODO: optionen holen
	
	$options = array(
			"api_host" => "85.214.202.153",
			"api_user" => "k.helbig",
			"api_pass" => "124578aa",
			"api_db"   => "shopware",
			"last_import_id" => 0,
			"split_order_positions" => 1,
			"assoc_vouchers" => 1
			);
	
	//// API_CONNECTION_DATA
	$api_shopware_host = $options["api_host"];
	$api_shopware_user = $options["api_user"];
	$api_shopware_pass = $options["api_pass"];
	$api_shopware_db   = $options["api_db"];
	
	$api_connid = @mysql_connect($api_shopware_host, $api_shopware_user, $api_shopware_pass, true) OR die("Error: ".mysql_error());
	mysql_select_db($api_shopware_db, $api_connid) OR die("Error: ".mysql_error());
	
	mysql_query("SET NAMES 'utf8'", $api_connid) OR die("Error: ".mysql_error());
	
	//// OPTIONS
	$last_import_id = $options["last_import_id"];
	
	//// VARIABLES
	$orders = array();
	$customers = array();
	$customer_billing_addresses = array();
	$customer_shipping_addresses = array();
	$order_positions = array();
	
	//// FETCH ORDERS
	$order_query = "
	SELECT * FROM s_order
	WHERE id > $last_import_id
	AND ordernumber > 0
	ORDER BY id ASC
	";
	$order_result = mysql_query($order_query, $api_connid) OR die("Error: ".mysql_error());
	while ($order = mysql_fetch_assoc($order_result))
	{
		$orders[$order["id"]] = new Order();
		if (!array_key_exists($order["userID"], $customers))
		{
			$customers[$order["userID"]] = new Customer();
			$orders[$order["id"]]->customer = $customers[$order["userID"]];
		}
		foreach ($attr_assoc["ot_order"] as $field_name => $attr)
		{
			if ($attr)
			{
				$orders[$order["id"]]->attributes->add($attr, $order[$field_name]);
			}
		}
	}
	
	//// FETCH CUSTOMERS
	$customer_query = "
	SELECT * FROM s_user WHERE id IN (".join(",", array_keys($customers)).")
	";
	$customer_result = mysql_query($customer_query, $api_connid) OR die("Error CUSTOMERFETCH: ".mysql_error());
	while ($customer = mysql_fetch_assoc($customer_result))
	{
		// UserID
		$customers[$customer["id"]]->attributes->add($attr_assoc["ot_customer"]["id"], $customer["id"]);
		// Email
		$customers[$customer["id"]]->attributes->add($attr_assoc["ot_customer"]["email"], $customer["email"]);
		// Newsletter
		$customers[$customer["id"]]->attributes->add($attr_assoc["ot_customer"]["newsletter"], $customer["newsletter"]);
	}


	//// FETCH CUSTOMER ADDRESSES
	// BILLING ADDRESSES
	$customer_addition_fields = array(
			"customernumber" => $attr_assoc["ot_customer_address"]["customernumber"],
			"salutation" => $attr_assoc["ot_customer_address"]["salutation"],
			"firstname" => $attr_assoc["ot_customer_address"]["firstname"],
			"lastname" => $attr_assoc["ot_customer_address"]["lastname"],
	);
	$customer_billing_address_query = "
	SELECT * FROM s_user_billingaddress WHERE userID IN (".join(",", array_keys($customers)).")
	";
	$customer_billing_address_result = mysql_query($customer_billing_address_query, $api_connid) OR die("Error: ".mysql_error());
	while ($customer_billing_address = mysql_fetch_assoc($customer_billing_address_result))
	{
		$billing_address = new CustomerAddress();
		$billing_address->type=0;
		$customers[$customer_billing_address["userID"]]->addresses->add($billing_address);
		foreach ($attr_assoc["ot_customer_address"] as $field_name => $attr)
		{
			if ($attr)
			{
				$billing_address->attributes->add($attr, $customer_billing_address[$field_name]);
			}
		}
		foreach ($customer_addition_fields as $field_name => $attr)
		{
			if ($attr)
			{
				$customers[$customer_billing_address["userID"]]->attributes->add($attr, $customer_billing_address[$field_name]);
			}
		}
	}

	// SHIPPING ADDRESSES
	$shipping_address_fields = array(
			"company" => $attr_assoc["ot_customer_address"]["company"],
			"salutation" => $attr_assoc["ot_customer_address"]["salutation"],
			"firstname" => $attr_assoc["ot_customer_address"]["firstname"],
			"lastname" => $attr_assoc["ot_customer_address"]["lastname"],
			"street" => $attr_assoc["ot_customer_address"]["street"],
			"streetnumber" => $attr_assoc["ot_customer_address"]["streetnumber"],
			"zipcode" => $attr_assoc["ot_customer_address"]["zipcode"],
			"city" => $attr_assoc["ot_customer_address"]["city"],
			"countryID" => $attr_assoc["ot_customer_address"]["countryID"],
			"stateID" => $attr_assoc["ot_customer_address"]["stateID"]
	);
	$customer_shipping_address_query = "
	SELECT * FROM s_user_shippingaddress WHERE userID IN (".join(",", array_keys($customers)).")
	";
	$customer_shipping_address_result = mysql_query($customer_shipping_address_query, $api_connid) OR die("Error: ".mysql_error());
	while ($customer_shipping_address = mysql_fetch_assoc($customer_shipping_address_result))
	{
		$shipping_address = new CustomerAddress();
		$shipping_address->type=1;
		$customers[$customer_shipping_address["userID"]]->addresses->add($shipping_address);
		foreach ($shipping_address_fields as $field_name => $attr)
		{
			if ($attr)
			{
				$shipping_address->attributes->add($attr, $customer_shipping_address[$field_name]);
			}
		}
	}

	//// ORDER POSITIONS
	$order_positions_query = "
	SELECT * FROM s_order_details WHERE orderID in (".join(",", array_keys($orders)).")
	";
	$order_positions_result = mysql_query($order_positions_query, $api_connid) OR die("Error: ".mysql_error());
	while ($s_order_details_row = mysql_fetch_assoc($order_positions_result))
	{
		// SPLIT ORDER POSITIONS IF AMOUNT > 1
		if ($options["split_order_positions"] == 1)
		{
			for ($i = 0; $i < $s_order_details_row["quantity"]; $i++)
			{
				$s_order_details_row["quantity"] = 1;
				$order_position = new Position();
				$orders[$s_order_details_row["orderID"]]->positions->add($order_position);
				foreach ($attr_assoc["ot_position"] as $field_name => $attr)
				{
					if ($attr)
					{
						$order_position->attributes->add($attr, $s_order_details_row[$field_name]);
					}
				}
			}
		}
		else
		{
			$order_position = new Position();
			$orders[$s_order_details_row["orderID"]]->positions->add($order_position);
			
			foreach ($attr_assoc["ot_position"] as $field_name => $attr)
			{
				if ($attr)
				{
					$order_position->attributes->add($attr, $s_order_details_row[$field_name]);
				}
			}			
		}
	}
		
	mysql_close($api_connid);

	//Order::bulk_save($orders);

	//// DEBUG
	$endtime = round(microtime(true),4);
	echo "Runtime ".substr(($endtime - $starttime),0,5)." seconds <br>";
	echo "last_import_id: ".$last_import_id."<br>";
	echo "orders: ".count($orders)."<br>";
	echo "customers: ".count($customers)."<br>";
	echo "customer_billing_addresses: ".count($customer_billing_addresses)."<br>";
	echo "customer_shipping_addresses: ".count($customer_shipping_addresses)."<br>";
	echo "order_positions: ".count($order_positions)."<br>";
	
	header("Location: $referer#api_shopware_tabs_import");

?>