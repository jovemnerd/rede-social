<?php

abstract class AbstractLogger{

	protected $_file;
	protected $_extension;
	
	public function setLogfile($filename)
	{
		$this->_file = LOG_DIR.$filename.".{$this->_extension}";
		
		if(!is_dir(TMP_DIR))
			mkdir(TMP_DIR,0777);
			
		if(!is_dir(LOG_DIR))
			mkdir(LOG_DIR,0777);
		
			
		if(!file_exists($this->_file))
			file_put_contents($this->_file,'');
	}
	
	abstract function message($msg);
		
	
}
