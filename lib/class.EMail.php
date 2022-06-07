<?
/**
 * @author Mayer Roman (majer.rv@gmail.com)
 */

class EMail {

	private $header = array();
	
	private $fake = false;
	
	/**
	 * @return EMail
	 */
	static function Factory() {
		return new self;
	}

	function addHeader($str) {
		$this->header[] = $str;
	}
	
	function add_header_charset($charset = 'utf-8') {
		$this->addHeader("Content-Type: text/html; charset=$charset");
	}
	
	function add_header_from($email) {
		$this->addHeader("From: $email");
	}


	function send($to, $subject, $message, $email="", $attach=false) {
		$headers = null;
		if (!empty($this->header)) {
			$headers = implode("\r\n", $this->header);
		}
		if (!$this->fake) {
			$res = $this->smtpmail($to, $subject, $message, $email="", $attach); 
		} else {
			$str = "$to, $subject, $message, $headers";
			$str = str_replace(', ', "\r\n", $str);
			$res = $this->fakeSend($str);
		}
		return $res;
	}


	function fakeSend($str) {
		$str .= "\r\n----------------------------\r\n";
		$filename = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'fake.txt';
		$handle = fopen($filename, 'a');
		fwrite($handle, $str);
		return 1;
	}


function smtpmail($to, $subject, $content, $email="", $attach=false, $attach_all_files_module = false){
	require_once('smtpconfig.php'); //путь до конфигурационного файла для вашего smtp сервера
	require_once('class.phpmailer.php'); //путь до класса phpmailer
	$mail = new PHPMailer(true);
	 
	$mail->IsSMTP();
	try {
	  $mail->Host       = $__smtp['host'];
	  $mail->SMTPDebug  = $__smtp['debug'];
	  $mail->SMTPAuth   = $__smtp['auth'];
	  $mail->Port       = $__smtp['port'];
	  $mail->Username   = $__smtp['username'];
	  $mail->Password   = $__smtp['password'];
	  $mail->CharSet = 'utf-8';
	  // $mail->AddReplyTo($__smtp['addreply'], "pitvoda.ru");
    if(is_array($to)){
      foreach($to as $k => $v){
        $mail->AddAddress($v);   
      }
    }else{
      $mail->AddAddress($to);                //кому письмо  
    }
    
	  
	         //кому письмо
	  $mail->SetFrom($__smtp['addreply'], $_SERVER['SERVER_NAME']); //от кого (желательно указывать свой реальный e-mail на используемом SMTP сервере
	  // $mail->AddReplyTo($__smtp['addreply'], $__smtp['username']);
	  if ($email) $mail->AddReplyTo($email);
	  $mail->Subject = htmlspecialchars($subject);
	  $mail->MsgHTML($content);
   
	  if(is_array($attach)){
      for($i=0;$i<count($attach['fileFF']['name']);$i++) {
        if(is_uploaded_file($attach['fileFF']['tmp_name'][$i])) {
           //$attachment = chunk_split(base64_encode(file_get_contents($attach['fileFF']['tmp_name'][$i])));
           //$filename = $attach['fileFF']['name'][$i];
           //$filetype = $attach['fileFF']['type'][$i];
           //$filesize += $attach['fileFF']['size'][$i];
           $mail->AddAttachment($attach['fileFF']['tmp_name'][$i], $attach['fileFF']['name'][$i]);
           
      }
     }
    }else if($attach)  $mail->AddAttachment($attach);
    
    if(is_array($attach_all_files_module)){
      foreach($attach_all_files_module as $mail_attachment){
        $mail->AddAttachment("images/all_files/files/".$mail_attachment['file'], $mail_attachment['title']); 
      }
    }else if($attach_all_files_module)  $mail->AddAttachment($attach_all_files_module);
	  return($mail->Send());
	  // echo "Message sent Ok!</p>\n";
	} 
	catch (phpmailerException $e) 
	{
	  echo $e->errorMessage();
	  // die();
	  // error_log($e->ErrorInfo);
	} 
	catch (Exception $e) 
	{
	  echo $e->getMessage();
	}
}
}
?>