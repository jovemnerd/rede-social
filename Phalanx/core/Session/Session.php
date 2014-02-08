<?php

	class Session{
	
		private $data;
		
		public function __construct(){
			$this->data = SessionData::getInstance();
		}
	
		public function id(){
			return $this->data->getId();
		}
		
		public function __set($k, $v){
			$this->data->set($k, $v);
		}
		
		public function __get($k){
			return $this->data->get($k);
		}
		
		public function destroy($k=null){
			$this->data->destroy($k);
		}
	}