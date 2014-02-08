<?php


class Logger{
		
	private static $instance;
	
	private function __construct($type){
		$type = strtoupper($type);
		if(file_exists(dirname(__file__).'/core/AbstractLogger.php'))
			require(dirname(__file__).'/core/AbstractLogger.php');
			
		if(file_exists(dirname(__file__).'/core/'.$type.'Logger.php'))
			require(dirname(__file__).'/core/'.$type.'Logger.php');
		
		$class = "{$type}Logger";
		
		if(class_exists($class))
			eval("self::\$instance = new {$class}();");
		else
			throw new PhxException("ClassFile {$class} not found");
	}
	
	static function getInstance($type=LOG_FILE_FORMAT){
		if(empty(self::$instance))
			new self($type);
		return self::$instance;
	}
	
}