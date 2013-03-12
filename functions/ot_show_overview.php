<h1><?php echo $data['title_plural']; ?></h1>

<?php if ($objects): ?>
	<table>
		<thead>
			<tr>
			<?php foreach ($fields as $name => $field): ?>
				<th><?php echo $field['title']; ?></th>
			<?php endforeach; ?>
			</tr>
		</thead>
		
		<tbody>
		<?php foreach ($objects as $object): ?>
			<tr class="entity" id="<?php echo $object->id; ?>">
				<?php foreach ($fields as $name => $field): ?>
					<?php if ($field['link']): ?>
						<td><a href="<?php echo $ot->get_link($data['module'], $object->id, isset($data['sub']) ? $data['sub'] : ''); ?>"><?php echo $object->$name; ?></a></td>
					<?php else: ?>
						<td><?php echo $object->$name; ?></td>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	
<?php else: ?>
	<p>Keine <?php echo $data['title_plural']; ?> vorhanden!</p>
	
<?php endif; ?>