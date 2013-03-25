TRUNCATE TABLE ot_position;
TRUNCATE TABLE ot_customer_address;
TRUNCATE TABLE ot_customer;
TRUNCATE TABLE ot_order;
DELETE FROM ot_value WHERE attribute_id IN (SELECT id FROM ot_attribute WHERE ref_table IN ('ot_position', 'ot_customer_address', 'ot_customer', 'ot_order'));
