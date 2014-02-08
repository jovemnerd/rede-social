<?php

require(dirname(__FILE__)."/defaultSetting.php");	
require(dirname(__FILE__)."/autoLoad.php");	

class Phalanx extends AppController{
			
	public function __construct(){ 
		self::handler();
	}
	
	public static function loadController(){
		$data = func_get_args();
		foreach($data as $d){
			if(!preg_match('#\.#i',$d)){
				$module = self::$MODULE[0];
			}else{
				$e = explode('.', $d);
				$module = $e[0];
				$d =  $e[1];
			}
			
			if(file_exists(APPLICATION_DIR.$module.SEPD.CONTROLLER_DIR.$d.'.php'))
				require_once(APPLICATION_DIR.$module.SEPD.CONTROLLER_DIR.$d.'.php');
		}
	}
	
	
	static public function loadClasses(){
		$data = func_get_args();
		foreach($data as $d){
			if(!preg_match('#\.#i',$d)){
				$module = self::$MODULE[0];
			}else{
				$e = explode('.', $d);
				$module = $e[0];
				$d 		=  $e[1];
			}
		
			if(file_exists(APPLICATION_DIR.$module.SEPD.CLASSES_DIR.$d.'.php'))
				include_once(APPLICATION_DIR.$module.SEPD.CLASSES_DIR.$d.'.php');
			else
				if(file_exists(APPLICATION_DIR.$module.SEPD.CLASSES_DIR.$d.'.class.php'))
					include_once(APPLICATION_DIR.$module.SEPD.CLASSES_DIR.$d.'.class.php');
			else
				if(file_exists(APPLICATION_DIR.$module.SEPD.CLASSES_DIR.$d.'.ini.php'))
					require_once(APPLICATION_DIR.$module.SEPD.CLASSES_DIR.$d.'.ini.php');
			else
				throw new PhxException("Fatal error: Class file not found ".APPLICATION_DIR.$module.SEPD.CLASSES_DIR.$d);
		}			
	}
	
	static public function loadExtension(){
		$data = func_get_args();
		foreach($data as $d){
			if(file_exists(dirname(__FILE__).'/../extensions/'.$d.'/'.$d.'.php'))
				require_once(dirname(__FILE__).'/../extensions/'.$d.'/'.$d.'.php');
			else
				if(file_exists(dirname(__FILE__).'/../extensions/'.$d.'/'.$d.'.class.php'))
					require_once(dirname(__FILE__).'/../extensions/'.$d.'/'.$d.'.class.php');
			else
				if(file_exists(dirname(__FILE__).'/../extensions/'.$d.'/'.$d.'.inc.php'))
					require_once(dirname(__FILE__).'/../extensions/'.$d.'/'.$d.'.inc.php');
			else
				if(file_exists(dirname(__FILE__).'/../extensions/'.$d.'/class.'.$d.'.php'))
					require_once(dirname(__FILE__).'/../extensions/'.$d.'/class.'.$d.'.php');
			else
				die("FATAL ERROR: Class file not found {$d}");
		}
	}
}
