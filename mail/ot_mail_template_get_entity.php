<?php $mail_template = MailTemplate::get($_GET['id']); ?>

<h1><?php echo $mail_template->name; ?></h1>

<form action="<?php $ot->get_link('mail', $mail_template->id, 'template'); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="action" value="save" />
	<fieldset>
		<legend>Text</legend>
		<textarea name="text" style="width: 100%;"><?php echo $mail_template->text; ?></textarea>
	</fieldset>
	<input type="submit" value="Speichern" />
</form>

<script type="text/javascript" src="static/tinymce/tiny_mce.js"></script>
<script>
	tinyMCE.init({
		theme: 'advanced',
		mode : 'textareas',
		height: '400',
	});
</script>