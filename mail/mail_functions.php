<?php
function convert_mime_to_text($element) {
	$data = array();
	foreach (imap_mime_header_decode($element) as $part) {
		$data[] = mb_convert_encoding($part->text, 'UTF-8');
	}
	return join(' ', $data);
} 
?>