<?php
function __autoload($classname){
	$file=null;
	
	if(file_exists((dirname(__FILE__)."/core/$classname/$classname.php")))$file = (dirname(__FILE__)."/core/$classname/$classname.php");
	else 
		if(file_exists((dirname(__FILE__)."/core/$classname/$classname.class.php")))$file = (dirname(__FILE__)."/core/$classname/$classname.class.php");
		else
			if(file_exists((dirname(__FILE__)."/core/$classname/$classname.ini.php")))$file = (dirname(__FILE__)."/core/$classname/$classname.ini.php");
	
	if(!is_null($file))	
		return require_once($file);
	
}

