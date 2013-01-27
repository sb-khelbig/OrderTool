<?php
class OrderToolLogger {
	private $queue = array();
	
	function add($action_id, $ref_id) {
		$timestamp = time();
		$this->queue[] = "($_SESSION[UserID], $ref_id, $action_id, $timestamp)";
	}
	
	function log() {
		if ($this->queue) {
			$values = join(', ', $this->queue);
			$result = mysql_query("	INSERT INTO ot_action_log
										(user_id, ref_id, action_id, timestamp_created)
										VALUES $values");
			if ($result) {
				$this->queue = array();
			} else {
				return false;
			}
		}
		return true;
	}
	
	function __destruct() {
		$this->log();
	}
}

$logger = new OrderToolLogger();