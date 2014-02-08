<?php

class Template{
	private $_template,
			$_template_dir,
			$_file,
			$view;
	
	
	public function __construct($template_dir=null,$template=null){
		$this->_template_dir = TEMPLATE_DIR.((is_null($template_dir))?TEMPLATE:$template_dir);
		$this->_file = (is_null($template))?TEMPLATE_FILE:$template;
		$this->_template = $this->_template_dir.SEPD.$this->_file;
	}
	
	public function setViews(Views $views){
		$this->view = $views;
	}
	
	private function loadViewContent(){
		$this->view->loadView();
	}
	
	public function loadTemplate(){
		if(file_exists($this->_template)) 
			return include_once($this->_template);
		else
			throw new Exception("Template file not found: {$this->_template}"); 
	}
	
	public function getViewsVars(){
		return $this->view->_data;	
	}

}