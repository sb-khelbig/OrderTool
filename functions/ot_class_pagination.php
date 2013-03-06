<?php
class PageError extends Exception {
	
}

class Paginator {
	private $zero;
	private $limit;
	private $data;
	
	public function __construct($limit, $table, $fields = array('*'), $conditions = array(), $zeroised = False) {
		$this->load_data($table, $fields, $conditions);
		$this->limit = $limit;
		$this->zero = ($zeroised) ? 0 : 1;
	}
	
	private function load_data($table, $fields, $conditions) {
		if ($conditions) {
			$conditions_ = array();
			foreach ($conditions as $field => $value) {
				$conditions_[] = "$field = $value";
			}
			$conditions_ = join(' AND ', $conditions_);
		} else {
			$conditions_ = 1;
		}
		$fields_ = join(', ', $fields);
		$result = mysql_query("	SELECT $fields_
								FROM $table
								WHERE $conditions_");
		if ($result) {
			while ($row = mysql_fetch_assoc($result)) {
				$this->data[] = $row;
			}
		} else {
			throw new MySQLError(mysql_error(), mysql_errno());
		}
	}
	
	private function validate_page($page) {
		if (($page >= $this->zero) && ($page <= $this->get_page_count())) {
			return $page;
		} else {
			throw new PageError("Page '$page' does not exist!");
		}
	}
	
	public function get_data_count () {
		return count($this->data);
	}
	
	public function get_page_count () {
		if ($this->zero) {
			return ceil($this->get_data_count() / $this->limit);
		} else {
			return floor($this->get_data_count() / $this->limit);
		}
	}
	
	public function get_page($page, $fields = 'ALL') {
		$page = $this->validate_page($page);
		$slice = array_slice($this->data, ($page - $this->zero) * $this->limit, $this->limit);
		if (is_string($fields)) {
			if ($fields == 'ALL') {
				return $slice;
			} else {
				$data = array();
				foreach ($slice as $row) {
					$data[] = $row[$fields];
				}
				return $data;
			}
		} elseif (is_array($fields)) {
			$data = array();
			foreach ($slice as $row) {
				$tmp = array();
				foreach ($fields as $field) {
					$tmp[] = $row[$field];
				}
				$data[] = $tmp;
			}
			return $data;
		}
	}
	
	public function get_pagination_menu($page, $link, $seperator = ' ', $frame = 3) {
		$page = $this->validate_page($page);
		$pages = $this->get_page_count();
		$menu = array();
		
		if ($page-$frame > $frame + $this->zero) {
			for ($p = $this->zero; $p < $frame + $this->zero; $p++) {
				$menu[] = "<a href=\"$link&show=$this->limit&page=$p\">$p</a>";
			}
			$menu[] = "<a>...</a>";
			for ($p = $page - $frame; $p < $page; $p++) {
				$menu[] = "<a href=\"$link&show=$this->limit&page=$p\">$p</a>";
			}
		} else {
			for ($p = $this->zero; $p < $page; $p++) {
				$menu[] = "<a href=\"$link&show=$this->limit&page=$p\">$p</a>";
			}
		}
		
		$menu[] = "<a class=\"current_page\">$page</a>";
		
		if ($page+$frame < $pages - $frame) {
			for ($p = $page + 1; $p < $page + $frame + 1; $p++) {
				$menu[] = "<a href=\"$link&show=$this->limit&page=$p\">$p</a>";
			}
			$menu[] = "<a>...</a>";
			for ($p = $pages - $frame + 1; $p < $pages + 1; $p++) {
				$menu[] = "<a href=\"$link&show=$this->limit&page=$p\">$p</a>";
			}
		} else {
			for ($p = $page + 1; $p < $pages + 1; $p++) {
				$menu[] = "<a href=\"$link&show=$this->limit&page=$p\">$p</a>";
			}
		}
		
		return join($seperator, $menu);
	}
}