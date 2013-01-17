<?php
$server = "imap.gmail.com";
$port = 993;
$box = "INBOX";
$flags = array('/imap', '/ssl');
$username = "ecommerce@salesbutlers.com";
$password = 'Salesbutlers2012+';

$mailbox = '{' . $server . ':' . $port . join('', $flags) . '}' . $box;

$imap = imap_open($mailbox, $username, $password) or die(imap_last_error());