<?php

class HTMLLogger extends AbstractLogger{
	
	protected $_body		= null;
	protected $_extension	= 'html';

	function message($msg){
		$body = '<p style="background:#eeeeee;">'."\n";
		$body .= "\t".'<b>'.date('Y-m-d H:i:s').'</b>'."\n";
		$body .= "\t".'<i><pre>'.$msg.'</pre></i>'."\n";
		$body .= '</p>'."\n";
		file_put_contents(LOG_DIR.$this->file .'.'. $this->_extension, $body, FILE_APPEND);
	}


}