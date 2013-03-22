<?php if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	session_start(); 
	
	include 'db/mysql.php';
	include 'db/tables.php';
	
	$mail = isset($_POST['mail']) ? $_POST['mail'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
	
	$pw = hash('sha256', $password);
	
	$users = User::filter(array('mail' => $mail, 'password' => $pw));
	
	if (count($users) == 1) {
		$user = array_pop($users);
		
		$_SESSION['user_id'] = $user->id;
		
		$redirect = "index.php" . (($_SERVER['QUERY_STRING']) ? "?$_SERVER[QUERY_STRING]" : "");
		header("Location: $redirect");
	} else {
		$error = "Fehlgeschlagen!";
		include 'static/head.php';
		include 'static/navigation.php';
	}
} ?>

<div>
	<form action="login.php<?php echo ($_SERVER['QUERY_STRING']) ? "?$_SERVER[QUERY_STRING]" : ''?>" method="POST" enctype="multipart/form-data">
		<table style="border: 1px solid black;">
				<?php if (isset($error)): ?>
				<tr id="error">
					<td colspan="2"><?php echo $error; ?></td>
				</tr>
				<?php endif; ?>
				<tr>
					<td><label>Benutzer:</label></td>
					<td><input type="text" name="mail" size="50" /></td>
				</tr>
				<tr>
					<td><label>Passwort:</label></td>
					<td><input type="password" name="password" size="50"></td>
				<tr>
					<td colspan="2" style="text-align: center;"><input type="submit" value="Login" /></td>
				</tr>
			</table>
	</form>
</div>