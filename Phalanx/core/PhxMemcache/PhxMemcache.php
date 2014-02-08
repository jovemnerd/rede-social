<?php

	if (USE_MEMCACHE == 0)
		define('DISABLE_CACHE', 1);

	class PhxMemcache {
		private static $connection;
		private static $instance;
	
		private static function getInstance() {
			self::$instance || self::$instance = new PhxMemcache();
			return self::$instance->connection;
		}
	
		private function __construct() {
			if(defined('DISABLE_CACHE')) return false;
			
			$this->connection = new Memcache();
			$this->connection->addServer(MEMCACHE_SERVER_1, MEMCACHE_PORT_1);
			$this->connection->addServer(MEMCACHE_SERVER_2, MEMCACHE_PORT_2);
			$this->connection->addServer(MEMCACHE_SERVER_3, MEMCACHE_PORT_3);
			
			return ($this->connection);
		}
	
		public static function get($key) {
			return ($instance = self::getInstance()) ? $instance->get($key) : null;
		}
	
		public static function set($key, $value, $expire=0) {
			return ($instance = self::getInstance()) ? $instance->set($key, $value, MEMCACHE_COMPRESSED, $expire) : null;
		}
	
		public static function delete($key) {
			return ($instance = self::getInstance()) ? $instance->delete($key) : null;
		}
	
		public static function flush() {
			return ($instance = self::getInstance()) ? $instance->flush() : null;
		}
	
	
	}
