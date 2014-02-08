<?php
class Views{
	public $_views=array(),$_data=array(),$_tpl;
	
		
	public function __construct($tpl=null){
		if($tpl instanceOf Template)
			$this->_tpl = $tpl;
	}
	
	public function __set($k, $v){
		$this->_data[$k] = $v;
	}
	
	public function __get($k){
		if(!empty($this->_data) && isSet($this->_data[$k]))
			return $this->_data[$k];
		else 
			return null;
	}
	
	public function loadView(){
		foreach($this->_views as $views){
			$view = APPLICATION_DIR.Phalanx::$MODULE[0].SEPD.VIEWS_DIR.$views;
			if(file_exists($view))
				include_once($view);
			else
				die("404 - View Not found: " .$view);
		}
	}
	
	public function display($view=null){
		if(! is_null($view))
			$this->_views[] = $view;
		
		$this->_tpl->setViews($this);
		$this->_tpl->loadTemplate();
	}
	
	public function render($view, $encoding='utf8'){
		$view = APPLICATION_DIR.Phalanx::$MODULE[0].SEPD.VIEWS_DIR.$view;
		if(file_exists($view)){
			ob_start();
				require($view);
				$content = ob_get_contents();
			ob_end_clean();
			return stripslashes(stripslashes($content));
		} else {
			throw new PhxException("404 - View Not found: " .$view);
		}
	}
	
	protected function cycleValues(){
		$values 	= func_get_args();
		$cValues	= count($values);
		if(!isSet($idx)) static $idx = 0;
		$vl = $values[$idx];
		$idx = ($idx>=($cValues-1))? 0 : $idx+1;
		return $vl;
	}
		
	public function closeUnclosedTags($unclosedString){ 
		preg_match_all("/<([^\/]\w*)>/", $closedString = $unclosedString, $tags); 
		for ($i=count($tags[1])-1;$i>=0;$i--){ 
			$tag = $tags[1][$i]; 
			if (substr_count($closedString, "</$tag>") < substr_count($closedString, "<$tag>"))
				$closedString .= "</$tag>"; 
		} 
		return $closedString; 
	}	
	
	public function removeAccents($str){
		$changethis = 	array('à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ü','ú','ÿ','À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','O','Ù','Ü','Ú','Ÿ',);
		$tothis = 		array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','O','U','U','U','Y',);
		return str_replace($changethis, $tothis, $str);	
	}
		
		
	public function __destruct(){
		unset($this);
	}
	
	public function destroy(){
		unset($this);
	}
		
}