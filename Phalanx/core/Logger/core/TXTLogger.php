<?php

class TXTLogger extends AbstractLogger{
	
	protected $_body		= null;
	protected $_extension	= 'txt';
	public 	  $file			='log';
	
	public function message($msg)
	{
		$body = "--------------------------------------------------------------\n\r";
		$body .= "[".date('Y-m-d H:i:s')."]"."\n\r".$msg."\n\r";
		file_put_contents(LOG_DIR.$this->file.'.txt',$body,FILE_APPEND);
	}


}