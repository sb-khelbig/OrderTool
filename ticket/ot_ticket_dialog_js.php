<script type="text/javascript">
<!--
function register_ticket_dialogs() {
	$('.create_ticket_button').each(function (index, obj) {
		button = $(obj);
		register_ticket_dialog(button);
	});
}

function register_ticket_dialog(button) {
	var dialog = $('#' + button.attr('id').replace('_button', '_dialog'));
	
	button.bind('click', function () {
		dialog.dialog('open');
	});

	dialog.dialog({
		title: 'Ticket erstellen',
		autoOpen: false,
		height: 'auto',
		width: 'auto',
		modal: true,
		buttons: {
			'Erstellen': function () {
				$('#create_ticket_form', dialog).submit();
			}
		}
	});
}
//-->
</script>