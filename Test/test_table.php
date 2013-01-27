<h1 id="headline">Beispieltabelle</h1>

<div class="overview">
	<form>
		<div class="actions">
			<label>
				Aktion:
				<select name="action">
					<option value="0">Löschen</option>
				</select>
			</label>
		</div>
		<div></div>
		<table class="table">
			<thead>
				<tr>
					<th class="action-select"><input id="action-toggle" type="checkbox" /></th>
					<th>Name</th>
					<th>Datum</th>
				</tr>
			</thead>
			<tbody>
				<tr class="even">
					<td class="action-select"><input class="action-selectbox" type="checkbox" name="_ids" /></td>
					<td>Max Mustermann</td>
					<td>Heute</td>
				</tr>
				<tr class="odd">
					<td class="action-select"><input class="action-selectbox" type="checkbox" name="_ids" /></td>
					<td>Max Mustermann</td>
					<td>Heute</td>
				<tr>
			</tbody>
		</table>
	</form>
</div>

<script>
$(document).ready(function () {
	$('#action-toggle').bind('click', function () {
		$checked = $(this).prop('checked');
		$('.action-selectbox', '.overview .table').prop('checked', $checked);
	});
});
</script>