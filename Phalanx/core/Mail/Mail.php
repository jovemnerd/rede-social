<?php

class Mail{
	
	public 	$SMTPServer 	= MAIL_HOST,
			$SMTPPort 		= MAIL_PORT,
			$sendmailFrom 	= MAIL_FROM;
	
	private $_TO 			= array(),
			$_body			= null,
			$_subject		= null,
			$_header		= null;
			
	public	$format 		= 'text/html';
	
	public function __construct()
	{
		if(!is_null($this->SMTPServer) && $this->SMTPServer!='localhost')
			ini_set('SMTP',$this->SMTPServer);
		
		if($this->SMTPPort!=25)
			ini_set('smtp_port',$this->SMTPPort);
			
		if(!is_null($this->sendmailFrom))
			ini_set('sendmail_from',$this->sendmailFrom);
	}

	public function addEmail($email){
		array_push($this->_TO,$email);
	}

	private function getHeader(){
		return 'From:' . MAIL_ALIAS .'<'.MAIL_FROM .'>' . "\r\n" .
			   'Reply-To:'.MAIL_ALIAS .'<'.MAIL_FROM.'>' . "\r\n" .
			   'Content-type: '.$this->format.'; charset='.DEFAULT_CHARSET."\r\n".
			   'MIME-Version: 1.0' . "\r\n".
			   "Return-Path: ".MAIL_FROM."\r\n".
			   $this->_header.
			   'X-Mailer: PHP' . phpversion();
	}
	
	public function setHeader($str){
		$this->_header .= $str."\r\n";
	}
	
	public function setBody($str){
		$this->_body = (string)$str;
	}

	public function setSubject($str){
		$this->_subject = (string)$str;
	}
	
	public function send(){
	
		foreach($this->_TO as $email)
		{
			if(!@mail($email,$this->_subject,$this->_body,$this->getHeader()))
				die("Failed to connect to mailserver at '{$this->SMTPServer}' port {$this->SMTPPort}, verify your 'SMTP' and 'smtp_port'");
			continue;
		}
		return true;
	}
	
	public function preview(){
		$return = null;
		foreach($this->_TO as $email)
		{
			$return .= 'To:'.$email
			."\n\r".'<br />Subject: '.$this->_subject
			."\n\r".'<br />Header: '.$this->getHeader()
			."\n\r".'<br />Body: '.$this->_body
			."\n\r".'<hr />';
			continue;
		}
		return print($return);
	}
	
}