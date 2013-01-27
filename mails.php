<?php include('mail/mail_connection.php'); include('mail/mail_functions.php'); ?>

<div style="min-height: 92%;">
	<div id="buttons" style="height: 50px; background: black; bottom-border: 2px solid black;">
		<form>
			<input type="button" value="Abrufen" />
			<input type="button" value="Verfassen" />
			<input type="button" value="Antworten" />
			<input type="button" value="Weiterleiten" />
			<input type="button" value="Löschen" />
		</form>
	</div>
	<div id="inbox" style="height: 87%; width: 300px; float: left; border-right: 2px solid black; overflow: auto;">
		<?php if ($mails = imap_search($imap, 'ALL')): ?>
			<?php foreach ($mails as $mail): ?>
				<?php 	$header = imap_fetch_overview($imap, $mail, 0);
						$header = $header[0];
						$uid = imap_uid($imap, $mail); ?>
				<div class="mail_info" style="border: 1px solid black; overflow: hidden; border-collapse: collapse;">
					<table>
						<tr class="from"><td><?php echo convert_mime_to_text($header->from); ?></td></tr>
						<tr class="subject" style="white-space: nowrap;"><td><a href="<?php echo "ajax/ajax_mail.php?id=$uid"; ?>" target="mail_content" style="text-decoration: none;"><?php echo convert_mime_to_text($header->subject); ?></a></td></tr>
						<tr class="time"><td><?php echo convert_mime_to_text($header->date); ?></td></tr>
					</table>
				</div>
			<?php endforeach; ?>	
		<?php else: ?>
			<div id="no_mails">Keine neuen Mails vorhanden!</div>
		<?php endif; ?>
	</div>
	<div id="mail_body" style="height: 87%; width: 100%; text-align: center; vertical-align: middle;">
		<iframe name="mail_content" style="position: inherit; width: 69%; height: 99%;" seamless></iframe>
	</div>
</div>

<script type="text/javascript">
function load_mail(id) {
	jQuery.get('ajax/ajax_mail.php?id='+id,
			function (data) {
				jQuery('#mail_content').replaceWith('<td id="mail_content" style="vertical-align: top;">'+data+'</td>');
			}, 'html');
};
</script>


