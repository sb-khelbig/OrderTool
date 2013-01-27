<?php if ($_SERVER["REQUEST_METHOD"]=='POST'): ?>
	<?php
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'add_account':
				$field_names = array('name', 'address', 'password', 'protocol', 'server', 'server_port', 'smtp', 'smtp_port');
				$fields = array();
				foreach ($field_names as $fn) {
					$fields[$fn] = (isset($_POST[$fn])) ? "'" . mysql_real_escape_string($_POST[$fn]) . "'" : "''";
				}
				$f = join(', ', $field_names);
				$values = join(', ', $fields);
				echo "INSERT INTO ot_mail_account ($f) VALUES ($values)";
				$result = mysql_query("INSERT INTO ot_mail_account ($f) VALUES ($values)") or die(mysql_error());
				break;
			default:
				echo "Unsupported action!";
		}
	}
	?>

<?php elseif ($_SERVER["REQUEST_METHOD"]=='GET'): ?>
	<?php $accounts = mysql_query("SELECT id, name, address FROM ot_mail_account"); ?>
	<h1>Mail-Accounts</h1>
	<table>
		<tr>
			<td>ID</td>
			<td>Name</td>
			<td>Adresse</td>
		</tr>
		<?php while ($account = mysql_fetch_assoc($accounts)): ?>
		<tr>
			<td><?php echo $account['id']; ?></td>
			<td><?php echo $account['name']; ?></td>
			<td><?php echo $account['address']; ?></td>
		</tr>
		<?php endwhile; ?>
	</table>
	<br/>
	<button id="open">Account hinzufügen</button>
	<div id="popup">
		<form action="index.php?p=ot_mail_accounts" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="action" value="add_account" />
			<table>
				<tr>
					<td>Name:</td>
					<td><input type="text" name="name" /></td>
				</tr>
				<tr>
					<td>Adresse:</td>
					<td><input type="email" name="address" /></td>
				</tr>
				<tr>
				
					<td>Passwort:</td>
					<td><input type="password" name="password" /></td>
				</tr>
				<tr>
					<td>Protokoll:</td>
					<td>
						<select name="protocol">
							<option value="1">IMAP</option>
							<option value="2">POP3</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Eingangs-Server:</td>
					<td><input type="text" name="server" /></td>
				</tr>
				<tr>
					<td>Port:</td>
					<td><input type="number" name="server_port" /></td>
				</tr>
				<tr>
					<td>Ausgangs-Server:</td>
					<td><input type="text" name="smtp" /></td>
				</tr>
				<tr>
					<td>Port:</td>
					<td><input type="number" name="smtp_port" /></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" value="Account anlegen" /></td>
				</tr>
			</table>
		</form>
	</div>
	
	<script type="text/javascript">
		function setport() {
			var protocol = $('select[name=protocol]');
			if (protocol.val() == 1) {
				$('input[name=server_port]').val('993');
			} else {
				$('input[name=server_port]').val('25');
			}
		}
		
		jQuery(document).ready(function () {
			$('select[name=protocol]').bind('change', setport);
			setport();
			$('input[type=text], input[type=password], input[type=email]').css('width', '300px');
		});

		$("#popup").dialog({
		    autoOpen: false,
		    height: "auto",
		    width: "auto",
		    modal: true
		});

		$("#open")
		.button()
		.click(function() {
		  $("#popup").dialog("open");
		});

		
	</script>

<?php else: ?>
	<div>
		<p>Method not supported.</p>
	</div>
<?php endif; ?>