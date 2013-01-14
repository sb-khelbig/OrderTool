<?php $_SESSION['UserID'] = 0; ?>

<?php include ('db/connection.php'); include ('db/functions.php'); include ('functions/html.php'); ?>

<html lang="de">
	<head>
		<title>OrderTool</title>
		<?php include ('static/head.php'); ?>
	</head>
	<body>
		<?php include('static/navigation.php'); ?>
		
		<div id="content">
		<?php if (isset($_GET['p'])): ?>
			<?php include ($_GET['p'] . '.php'); ?>
		<?php else: ?>
			<p>Herzlich Willkommen</p>
		<?php endif; ?>
		</div>
	</body>
</html>