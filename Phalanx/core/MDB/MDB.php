<?php 

require(dirname(__file__).'/core/driver.interface.php');
require(dirname(__file__).'/drivers/'.DATABASE_ENGINE.'.php');

	class MDB{
			
		private static $instances = array();
		
		private function __construct(){}
		
		static function cursor($tablename){
			
			if(! self::$instances[$tablename]){
				self::$instances[$tablename] = self::getInstance();
				self::$instances[$tablename]->from($tablename);	
			}
			self::$instances[$tablename]->clear();
			return self::$instances[$tablename];
		}
		
		private static function getInstance(){
			$driver = DATABASE_ENGINE;
			return new $driver();
		}
	}