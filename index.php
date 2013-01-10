<html>
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