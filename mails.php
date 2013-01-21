<?php include('mail/mail_connection.php'); include('mail/mail_functions.php'); ?>

<table style="padding: 0; margin: 0;" >
	<tr>
		<td class="mails" style="padding: 0; margin: 0;">
			<?php if ($mails = imap_search($imap, 'ALL')): ?>
			<table border="1" style="width: 200px;">
			<?php foreach ($mails as $mail): ?>
				<?php 	$header = imap_fetch_overview($imap, $mail, 0);
						$header = $header[0]; ?>
				<tr>
					<td onclick="load_mail(<?php echo imap_uid($imap, $mail); ?>)" style="cursor: pointer;"><?php echo convert_mime_to_text($header->subject) . '<br />' . convert_mime_to_text($header->from) . ' | ' . convert_mime_to_text($header->date); ?></td>
				</tr>
			<?php endforeach; ?>
			</table>
			<?php endif; ?>
		</td>
		<td id="mail_content" style="vertical-align: top;"></td>
	</tr>
</table>

<script type="text/javascript">
function load_mail(id) {
	jQuery.get('ajax/ajax_mail.php?id='+id,
			function (data) {
				jQuery('#mail_content').replaceWith('<td id="mail_content" style="vertical-align: top;">'+data+'</td>');
			}, 'html');
};
</script>