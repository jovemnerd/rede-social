<?php
	class BBCode{
		public static function parse($string, array $allowed_tags) { 
	        $tags = implode('|', array_keys($allowed_tags));
			 
	        while (preg_match_all('`\[('.$tags.')=?(.*?)\](.+?)\[/\1\]`', $string, $matches)) foreach ($matches[0] as $key => $match) { 
	            list($tag, $param, $innertext) = array($matches[1][$key], $matches[2][$key], $matches[3][$key]); 
	            
				$replacement = $allowed_tags[$tag][0] . $innertext . $allowed_tags[$tag][1];
				
	            $string = str_replace($match, $replacement, $string); 
	        } 
	        return $string; 
	    } 
		
	}
