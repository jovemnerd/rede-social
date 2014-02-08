<?php

abstract class AppController{
	static public $MODULE = null;
	static public $CONTROLLER = null;
	static public $ACTION = null;
	static private $_urlpatterns = array();
	
	static function urlPatterns($actions){
		self::$_urlpatterns[] = array($actions);
	}
	
	static private function patterns($actions){
		foreach($actions as $pcre => $ctrStr){
			$matches = array();
			
			$url = parse_url(URI);
			$request_url = $url['path']; 
																																									
			if(preg_match("#^\/?{$pcre}\/?$#i", $request_url, $matches)){
				foreach($matches as $k => $v) if(! is_numeric($k) and $k != 'querystring') $_GET[$k] = $v;
				return self::controller($ctrStr);
			}
		}
		return false;
	} 
	
	
	private static function controller($ctrStr){
		$exp 	= explode('.',$ctrStr);
		$action = $exp[2];
		$ctr 	= $exp[1];
		$app 	= $exp[0];

		self::$MODULE[]		= $app;
		self::$CONTROLLER[]	= $ctr;
		self::$ACTION[]		= $action;

		$file = APPLICATION_DIR."{$app}".SEPD.CONTROLLER_DIR."{$ctr}.php";

		if(!file_exists($file))
			throw new PhxException("file ({$file}) not found");

		require($file); 

		$obj_crl = ucfirst($ctr);

		if(!class_exists($obj_crl))
			throw new PhxException("Controller ($obj_crl) not found");

		$objContr = New $obj_crl();
		if(!method_exists($objContr,$action))
			throw new PhxException("Action ( {$obj_crl}::{$action} ) not found");

		$objContr->$action();
		
		return true;
	
	}
	
	static protected function handler(){
		try{
			$found_a_match = false;
			foreach(self::$_urlpatterns as $apps){
				if(self::patterns($apps[0]) == false){
					continue;
				} else {
					$found_a_match = true;
				}
				return;
			}
			
			if($found_a_match === false)
				throw new PhxException('404');
			
		}catch(Exception $e){
			switch($e->getMessage()){
				case '404':
					$v = new Views(new Template('error', '404.phtml'));
					$v->display();
					break;
					
				default:
					echo "<h3>Error</h3>";
					echo "<p>{$e->getMessage()}</p>";
					die();
			}
		}
	}
}
