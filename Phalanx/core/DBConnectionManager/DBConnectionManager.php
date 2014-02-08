<?php

	class DBConnectionManager{
		
		private static $connections = array();
		
		public static function get_connection($host, $user, $password, $dbname){
			$key = md5($host.$user.$pass.$dbname);
			
			if(! array_key_exists($key, self::$connections)){
				$conn = mysqli_connect($host, $user, $password);
				if(! $conn) throw new PhxException("Could not connect to the database:" . mysqli_connect_error());
				
				$dbsel = mysqli_select_db($conn, $dbname);
				if(! $dbsel) throw new PhxException("Could not select the database:" . mysqli_connect_error());
				
				self::$connections[$key] = $conn;
			}
			
			return self::$connections[$key];
		}
		
		public static function getProperConnection($sql){
			if(preg_match('/^[SELECT|CALL]/i', $sql)){
				$connection = self::get_connection(SLAVE_DATABASE_HOST, SLAVE_DATABASE_USER, SLAVE_DATABASE_PASSWORD, SLAVE_DATABASE_NAME);
			} else{
				$connection = self::get_connection(MASTER_DATABASE_HOST, MASTER_DATABASE_USER, MASTER_DATABASE_PASSWORD, MASTER_DATABASE_NAME);
			}
			
			return $connection;
		}
		
		public static function getMasterConnection(){
			return self::get_connection(MASTER_DATABASE_HOST, MASTER_DATABASE_USER, MASTER_DATABASE_PASSWORD, MASTER_DATABASE_NAME);
		}
		
		public static function getSlaveConnection(){
			return self::get_connection(SLAVE_DATABASE_HOST, SLAVE_DATABASE_USER, SLAVE_DATABASE_PASSWORD, SLAVE_DATABASE_NAME);
		}
		
	}