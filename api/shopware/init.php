<?php

$data_source_id = 1;

$attribute_array = array(
		"ot_order" => array(
				"id" => "BestellID",
				"ordernumber" => "Ordernummer",
				"userID" => "NutzerID",
				"invoice_amount" => "Rechnungsbetrag (Brutto)",
				"invoice_amount_net" => "Rechnungsbetrag (Netto)",
				"invoice_shipping" => "Versandkosten (Brutto)",
				"invoice_shipping_net" => "Versandkosten (Netto)",
				"ordertime" => "Bestelldatum",
				"status" => "Bestellstatus",
				"cleared" => "Zahlstatus",
				"transactionID" => "TransaktionsID",
				"currency" => "Währung"
		),
		"ot_customer" => array (
				"id" => "NutzerID",
				"customernumber" => "Nutzernummer",
				"email" => "E-Mail Adresse",
				"newsletter" => "Newsletternutzung",
				"salutation" => "Anrede",
				"firstname" => "Vorname",
				"lastname" => "Nachname"
		),
		"ot_customer_address" => array (
				"company" => "Firma",
				"salutation" => "Anrede",
				"firstname" => "Vorname",
				"lastname" => "Nachname",
				"street" => "Straße",
				"streetnumber" => "Hausnummer",
				"zipcode" => "PLZ / ZIP",
				"city" => "Stadt",
				"phone" => "Telefonnummer",
				"fax" => "Fax",
				"country" => "Land",
				"state" => "Bundesland",
				"ustid" => "UmsatzsteuerID"
		),
		"ot_position" => array (
				"id" => "PositionsID",
				"orderID" => "OrderID",
				"ordernumber" => "Ordernummer",
				"articleID" => "ArtikelID",
				"articleordernumber" => "SKU",
				"price" => "Preis (Brutto)",
				"quantity" => "Anzahl",
				"name" => "Artikelname",
				"modus" => "Modus",
				"tax_rate" => "MwSt. Satz"
		)
);


$values = array();
foreach ($attribute_array as $table => $fields)
{
	foreach ($fields as $field_name => $field_title)
	{
		$values[] = "($data_source_id, '$field_name', '$field_title', '$table')";
	}
}

$query = "
		INSERT INTO ot_data_source_has_attribute
		(data_source_id, field_name, field_title, ref_table)
		VALUES
		".join(",", $values);

MySQL::query($query);

?>