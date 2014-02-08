<?php

abstract class Controller{
	public function __construct(){	
		$this->init();
	}
	
	abstract function init();	
}
