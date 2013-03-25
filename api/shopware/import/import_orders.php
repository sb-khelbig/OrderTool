<?php
	echo "<pre>";
	$data_source = DataSource::get($_GET["id"]);
	$options = $data_source->getOptionsArray();
	
	$referer = $_SERVER["HTTP_REFERER"];
	set_time_limit(0);
	ini_set("memory_limit", "1024M");
	
	include __DIR__ . '/../db_connection.php';
	
	$starttime = round(microtime(true),4);
	
	//// FETCH ATTRIBUTE_ASSIGNMENTS
	$attr_assoc = $data_source->getAssocArray();

	$last_import_id = $options["last_order_import_id"];
	
	//// VARIABLES
	$orders = array();
	$customers = array();
	$customer_billing_addresses = array();
	$customer_shipping_addresses = array();
	$order_positions = array();
	
	//// FUNCTIONS
	function add_order_position($position_row_assoc, $attr_assoc, &$orders, &$order_positions) {
		$order_positions[$position_row_assoc["orderID"]][$position_row_assoc["id"]] = new Position();
		$orders[$position_row_assoc["orderID"]]->positions->add($order_positions[$position_row_assoc["orderID"]][$position_row_assoc["id"]]);
		foreach ($attr_assoc["ot_position"] as $field_name => $attr) {
			if ($attr) {
				if (isset($position_row_assoc[$field_name])) {
					$order_positions[$position_row_assoc["orderID"]][$position_row_assoc["id"]]->attributes->add($attr, $position_row_assoc[$field_name]);
				}
			}
		}
		return $order_positions[$position_row_assoc["orderID"]][$position_row_assoc["id"]];
	}
		
	//// FETCH ORDERS
	$order_query = "
		SELECT * FROM s_order AS o
		LEFT JOIN s_order_billingaddress AS a ON o.id = a.orderID
		WHERE o.id > $last_import_id
		AND o.ordernumber > 0
		AND a.id != 'NULL'
		AND a.userID != 'NULL'
		ORDER BY o.id ASC
	";
	$order_result = MySQL_extern::query($order_query);
	while ($order = MySQL_extern::fetch($order_result))
	{
		$orders[$order["orderID"]] = new Order();
		$orders[$order["orderID"]]->data_source = $data_source;
		 
		if (!array_key_exists($order["userID"], $customers))
		{
			$customers[$order["userID"]] = new Customer();
		}
		
		$orders[$order["orderID"]]->customer = $customers[$order["userID"]];
		
		foreach ($attr_assoc["ot_order"] as $field_name => $attr)
		{
			if ($attr)
			{
				$orders[$order["orderID"]]->attributes->add($attr, $order[$field_name]);
			}
		}
	}
	
	//// FETCH CUSTOMERS
	$customer_query = "
	SELECT *
	FROM s_user
	WHERE id IN (".join(",", array_keys($customers)).")
	";
	$customer_result = MySQL_extern::query($customer_query);
	while ($customer = MySQL_extern::fetch($customer_result))
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
			"customernumber" => $attr_assoc["ot_customer"]["customernumber"],
			"salutation" => $attr_assoc["ot_customer"]["salutation"],
			"firstname" => $attr_assoc["ot_customer"]["firstname"],
			"lastname" => $attr_assoc["ot_customer"]["lastname"],
	);
	$customer_addition_done = array();
	$customer_billing_address_query = "
	SELECT * FROM s_order_billingaddress WHERE orderID IN (".join(",", array_keys($orders)).")
	";
	$customer_billing_address_result = MySQL_extern::query($customer_billing_address_query);
	while ($customer_billing_address = MySQL_extern::fetch($customer_billing_address_result))
	{
		// TODO: fetch country and state names with join
		$customer_billing_address["country"] = $customer_billing_address["countryID"];
		$customer_billing_address["state"] = $customer_billing_address["stateID"];
		
		$billing_address = new CustomerAddress();
		$billing_address->type=0;
		$orders[$customer_billing_address['orderID']]->addresses->add($billing_address);
		$customers[$customer_billing_address["userID"]]->addresses->add($billing_address);
		foreach ($attr_assoc["ot_customer_address"] as $field_name => $attr)
		{
			if ($attr)
			{
				$billing_address->attributes->add($attr, $customer_billing_address[$field_name]);
			}
		}
		
		if (!array_key_exists($customer_billing_address["userID"], $customer_addition_done)) {
			$customer_addition_done[$customer_billing_address["userID"]] = TRUE;
			foreach ($customer_addition_fields as $field_name => $attr)
			{
				if ($attr)
				{
					$customers[$customer_billing_address["userID"]]->attributes->add($attr, $customer_billing_address[$field_name]);
				}
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
			"country" => $attr_assoc["ot_customer_address"]["country"],
			"state" => $attr_assoc["ot_customer_address"]["state"]
	);
	$customer_shipping_address_query = "
	SELECT * FROM s_order_shippingaddress WHERE orderID IN (".join(",", array_keys($orders)).")
	";
	$customer_shipping_address_result = MySQL_extern::query($customer_shipping_address_query);
	while ($customer_shipping_address = MySQL_extern::fetch($customer_shipping_address_result))
	{
		// TODO: fetch country and state names with join
		$customer_shipping_address["country"] = $customer_shipping_address["countryID"];
		$customer_shipping_address["state"] = $customer_shipping_address["stateID"];
		
		$shipping_address = new CustomerAddress();
		$shipping_address->type=1;
		$orders[$customer_shipping_address['orderID']]->addresses->add($shipping_address);
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
	SELECT position.*, voucher.restrictarticles, code.code, article.supplierID
	FROM s_order_details AS position
	LEFT JOIN s_emarketing_voucher_codes AS code
			ON (position.articleID = code.id)
	LEFT JOIN s_emarketing_vouchers AS voucher
			ON (code.voucherID = voucher.id)
        LEFT JOIN s_articles AS article
        		ON (article.id = position.articleID)
	WHERE orderID IN (".join(",", array_keys($orders)).")
	ORDER BY orderID";
	
	$order_positions_result = MySQL_extern::query($order_positions_query);
	
	$articles = array();
	while ($s_order_details_row = MySQL_extern::fetch($order_positions_result))
	{
		if ($s_order_details_row['modus'] == 2) { // Voucher
			if ($s_order_details_row["restrictarticles"] != "NULL") {
				foreach (explode(";", $s_order_details_row["restrictarticles"]) as $article) {
					if (array_key_exists($article, $articles[$s_order_details_row['orderID']])) {
						$articles[$s_order_details_row['orderID']][$article]->attributes->add($attr_assoc['ot_position']['voucher_code'], $s_order_details_row['code']);
						break;
					}
				}
			} else {  // Warenkorbgutschein
				//TODO: Warenkorbgutscheine behandeln
			}
		} else { // Article
			
			// SPLIT ORDER POSITIONS IF AMOUNT > 1
			if ($options["split_order_positions"] == 1) {
				$quantity = $s_order_details_row["quantity"];
				$s_order_details_row["quantity"] = "1";
					
				for ($i = 0; $i < $quantity; $i++) {
					$current = add_order_position($s_order_details_row, $attr_assoc, $orders, $order_positions);
				}
			} else {
				$current = add_order_position($s_order_details_row, $attr_assoc, $orders, $order_positions);
			}
			
			$articles[$s_order_details_row['orderID']][$s_order_details_row['articleordernumber']] = $current;
		}
	}
	
	$endtime = round(microtime(true),4);
	
	$starttime_save = round(microtime(true),4);
	Order::bulk_save($orders);
	$endtime_save = round(microtime(true),4);

	//// DEBUG
	echo "Runtime FETCH".substr(($endtime - $starttime),0,5)." seconds <br>";
	echo "Runtime SAVE".substr(($endtime_save - $starttime_save),0,5)." seconds <br>";
	echo "last_import_id: ".$last_import_id."<br>";
	echo "orders: ".count($orders)."<br>";
	echo "customers: ".count($customers)."<br>";
	echo "orders in postition_array: ".count($order_positions)."<br>";
	
	header("Location: $referer#api_shopware_tabs_import");

?>