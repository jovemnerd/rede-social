<?php

	class Cookies{
	
		private $_domain = '',
				$_path = '/',
				$_expire = 0,
				$secure = false,
				$httponly = false,
				$__data = array();
		
		public function __construct(){
			foreach($_COOKIE as $k => $v)
				$this->__data[$k] = $v;
		}
		
		public function setDomain($str){
			$this->_domain = (String) $str;
		}
		
		public function setExpire($int){
			$this->_expire = (int) $int;
		}
	
		public function setPath($str){
			$this->_path = (String) $str;
		}
		
		public function destroy(){
			$args = func_get_args();
			foreach($args as $k){
				if(isSet($_COOKIE[$k])){
					unset($this->$k);
					unset($_COOKIE[$k]);
					setcookie($k);
				}
			}
		}
		
		public function __set($k, $v){
			$exp = ($this->_expire == 0) ? time()+3600 : $this->_expire;
			
			$s = setcookie($k, $v, $exp, '/');
			$this->__data[$k] = $v;	
		}
		
		public function __get($k){
			return $_COOKIE[$k];
		}
		
		public function __destruct(){
			#unset($this);
		}
	}