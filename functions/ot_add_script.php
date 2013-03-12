<script>
	jQuery(document).ready(function () {
		var dialog = $('#add_dialog');
		
		dialog.dialog({
			title: '<?php echo $data['title']; ?> hinzufügen',
			autoOpen: false,
			height: 'auto',
			width: 'auto',
			modal: true,
			buttons: {
				'Hinzufügen': function () {
					$('#add_form').submit();
				}
			}
		});

		$('#add').bind('click', function () {
			dialog.dialog('open');
		});
		
	});
</script>