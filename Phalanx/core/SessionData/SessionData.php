<?php

	class SessionData {
		
		private $session_id;
		private $data;
		private static $instance;
		
		private function __construct(){
			$session_id = session_id(); 
			if(empty($session_id))
				session_start();
				
			$this->session_id = session_id();
		
			$this->data = array();
			foreach($_SESSION as $k => $v)
				$this->data[$k] = $v;
		}
		
		public static function getInstance(){
			if(is_null(self::$instance))
				self::$instance = new self();
			
			return self::$instance;
		}
		
		public function getId(){
			return $this->session_id;
		}
		
		public function get($k){
			return $this->data[$k];
		}
		
		public function set($k, $v){
			$_SESSION[$k] = $v;
			$this->data[$k] = $v;
		}
		
		public function destroy($k=null){
			if(!is_null($k)){
				unset($_SESSION[$k]);
				unset($this->data[$k]);
			}else{	
				session_unset();
				session_destroy();
				unset($_SESSION);
				unset($this);
			}
		}
		
		public function __destruct(){
			unset($this);
		}
		
	}

?>