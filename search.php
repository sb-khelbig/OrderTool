<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<form action="index.php?p=search" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="search" />
		<input type="text" name="searchstring" placeholder="Suchfeld" />
		<input type="submit" value="Suchen" />
	</form>
	<?php
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			
			case 'search':
				$searchstring = (isset($_POST['searchstring'])) ? $_POST['searchstring'] : '';
				$terms = explode(' ', $searchstring);
				$query = array();
				foreach ($terms as $term) {
					$query[] = "LOWER(c.data) LIKE LOWER('%$term%')";
				}
				$values = join(' OR ', $query);
				$result = mysql_query("	SELECT r.id AS id
										FROM ot_row AS r, ot_column AS c
										WHERE r.id = c.row_id
											AND ($values) GROUP BY r.id") or die(mysql_error());
				$ids = array();
				while ($row = mysql_fetch_assoc($result)) {
					$ids[] = $row['id'];
				}
				
				if ($ids) {
					$orders = join(', ', $ids);
					$result = mysql_query(" SELECT h.name AS header, c.data AS data, r.id AS id, o.name AS liste
							FROM ot_header AS h, ot_order_list_has_header AS olh, ot_row AS r, ot_column AS c, ot_order_list AS o
							WHERE h.id = olh.header_id
							AND olh.order_list_id = o.id
							AND olh.pos = c.pos
							AND olh.order_list_id = r.order_list_id
							AND r.id = c.row_id
							AND r.id IN ($orders)") or die(mysql_error());
					$last_id = 0;
					
					$output = array();
					while ($row = mysql_fetch_assoc($result)) {
						if (array_key_exists($row['id'], $output)) {
							$output[$row['id']][] = $row;
						} else {
							$output[$row['id']] = array($row);
						}
					} 
					
					foreach ($output as $row => $columns): ?>
						<div class="search_result" style="border: 1px solid black; margin-top: 10px; padding: 5px; width: 60%;">
							<a href="<?php echo "index.php?p=order&id=$row" ?>" target="_blank">Liste: <?php echo $columns[0]['liste']; ?></a>
							<ul>
							<?php foreach ($columns as $column): ?>
								<li><?php echo "$column[header]: $column[data]"; ?></li>
							<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach;
				} else { ?>
					<p>Suche ergab keine Treffer!</p>
				<?php
				}
				break;
		}
	}?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<form action="index.php?p=search" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="search" />
		<input type="text" name="searchstring" placeholder="Suchfeld" />
		<input type="submit" value="Suchen" />
	</form>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>
