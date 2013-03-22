<?php include __DIR__ . '/lib/class.phpmailer.php';

class SMTPException {
	
}

class OrderToolMailer {
	private $host;
	private $username;
	private $password;
	private $encryption;
	private $charset;
	
	public function __construct($host, $username, $password, $encryption='tls', $charset='utf-8') {
		$this->set_config($host, $username, $password, $encryption, $charset);
	}
	
	private function get_mailer() {
		$mailer = new PHPMailer();
		$mailer->IsSMTP();
		$mailer->SMTPAuth = true;
		$mailer->SMTPSecure = $this->encryption;
		$mailer->CharSet = $this->charset;
		$mailer->Host = $this->host;
		$mailer->Username = $this->username;
		$mailer->Password = $this->password;
		return $mailer;
	}
	
	public function set_config($host, $username, $password, $encryption='tls', $charset='utf-8') {
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->encryption = $encryption;
		$this->charset = $charset;
	}
	
	public function send_mail($from, $to, $subject, $content=array(), $bcc=array(), $cc=array(), $attachments=array()) {
		$mailer = $this->get_mailer();
		
		// Sender
		if (is_array($from)) {
			$mailer->SetFrom($from['address'], $from['name']);
		} else {
			$mailer->SetFrom($from);
		}
		
		// Receiver
		$mailer->AddAddress($to);
		
		// Subject
		$mailer->Subject = $subject;
		
		// BCC
		if ($bcc) {
			if (is_array($bcc)) {
				foreach ($bcc as $adr) {
					$mailer->AddBCC($adr);
				}
			} else {
				$mailer->AddBCC($bcc);
			}
		}
		
		// CC
		if ($cc) {
			if (is_array($cc)) {
				foreach ($cc as $adr) {
					$mailer->AddCC($adr);
				}
			} else {
				$mailer->AddCC($cc);
			}
		}
		
		// Content
		if ($content) {
			// HTML
			if (array_key_exists('html', $content)) {
				$mailer->MsgHTML($content['html']);
			} else {
				$mailer->MsgHTML('');
			}
			
			// Text
			if (array_key_exists('text', $content)) {
				$mailer->AltBody = $content['text'];
			}
			
			// Attachments
			if (array_key_exists('attachments', $content)) {
				$attachments = $content['attachments'];
				if ($attachments) {
					if (is_array($attachments)) {
						foreach ($attachments as $name => $path) {
							$mailer->AddAttachment($path, $name);
						}
					} else {
						$mailer->AddAttachment($attachments);
					}
				}
			}
		} else {
			die('Content empty!');
		}
		
		// Sending
		return array('success' => $mailer->Send(), 'error' => $mailer->ErrorInfo);
	}
}