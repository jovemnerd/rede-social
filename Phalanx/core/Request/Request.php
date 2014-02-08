<?php

define('STRIP', 'STRIP');
define('REPLACE', 'REPLACE');

	class Request{
	
		public static function post($striptags=true, $method=STRIP){
			$o = new stdClass;
			foreach($_POST as $k => $v){
				if(is_string($v)){
					$o->$k = addslashes(($striptags) ? ($method == STRIP) ? strip_tags($v) : htmlspecialchars($v) : $v);
				} else if(is_array($v)) {
					$a = new stdClass;
					foreach($v as $kv => $vv)
						$a->{$kv} = addslashes(($striptags) ? ($method == STRIP) ? strip_tags($vv) : htmlspecialchars($vv) : $vv);
					
					$o->{$k} = $a;
				}
			}
			return $o;
		}
		
		public static function files(){
			$o = new stdClass;
			foreach($_FILES as $k => $v){
				$file = $v;
				if(is_array($v)){
					$file = new stdClass;
					foreach($v as $key => $value){
						$file->$key = $value;
					}
				}
				$o->$k = $file;
			}
			return $o;
		}
	
		public static function get(){
			$o = new stdClass;
			foreach($_GET as $k => $v)
				$o->$k = addslashes($v);
			return $o;
		}
	
		public static function method(){
			return $_SERVER['REQUEST_METHOD'];
		}

		public static function isAjax(){
			return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
		}
	
		public static function redirect($url){	
			if (! headers_sent())
				header("Location: {$url}");
			else
				exit("<script type=\"text/javascript\" language=\"javascript\"> window.location.href='{$url}'/</script>");
			
		}
	
		public static function redirect_301($url){
			header('HTTP/1.1 301 Moved Permanently'); 
			header("Location: {$url}"); 
		}
	
		public static function status(){
			return $_SERVER['REDIRECT_STATUS'];
		}
	
		public static function http_accept(){
			$HTTP_ACCEPT = $_SERVER['HTTP_ACCEPT'];
			return (Array) explode(";",$HTTP_ACCEPT);
		}
			
		public function makeAsyncRequest($url, $params=null){
		    foreach ($params as $key => &$val) {
		      if (is_array($val)) $val = implode(',', $val);
		        $post_params[] = $key.'='.urlencode($val);
		    }
		    $post_string = implode('&', $post_params);
		
		    $parts=parse_url($url);
		
		    $fp = fsockopen($parts['host'],
		        isset($parts['port'])?$parts['port']:80,
		        $errno, $errstr, 30);
		
		    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
		    $out.= "Host: ".$parts['host']."\r\n";
		    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		    $out.= "Content-Length: ".strlen($post_string)."\r\n";
		    $out.= "Connection: Close\r\n\r\n";
		    if (isset($post_string)) $out.= $post_string;
		
		    fwrite($fp, $out);
		    fclose($fp);
		}

	}