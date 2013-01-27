<html>
	<head>
		<title>Test</title>
		<?php include 'static/head.php'; ?>
	</head>
	<body>
		<p id="data">Daten</p>
		<button id="load">Daten Laden</button>
		
		<script>
			function load_data(data) {
				alert(data);
				$('#data').html(data);
			};

			jQuery(document).ready(function () {
				$('#load').bind('click', function () {
					jQuery.get('ajax/ajax_db.php',
								{action: 'select', table: 'ot_header'},
								load_data);
				});
			});
		</script>
	</body>
</html>