<?php
class Cache
{

	static private 	$cachefile		=	null,
					$cache_content	=	null;

	// force cache
	static public 	$force_cache	=	false;

	// version
	static private 	$version = '1.3.0'; 

	static private 	$start = false;
	
	// get content by buffler
	static function start()
	{
		return ob_start();
	}

	static function caching($id_cache=false)
	{
		
		if($id_cache==false){
			ob_end_flush();
			die('Warning: Please set id for cache!');
		}
		
		$cache = strtolower(trim($id_cache));
		
		self::$cachefile = CACHE_DIR.$cache;
		
		if(file_exists(self::$cachefile))
			if(self::$force_cache==false)
				die(file_get_contents(self::$cachefile));
				
	}

	static function destroy($id_cache)
	{
			$file = strtolower(trim(CACHE_DIR.$id_cache));
			if(file_exists($file))
				return unlink($file);
			return false;
	}

	// get size of cache file in kbs
	static function getSize($id_cache)
	{
		$cache = strtolower(trim($id_cache));
		return ceil(filesize(CACHE_DIR.$cache)/1000)." Kbs";
	}

	// get content by buffler and create file cache
	static function cache_execute()
	{
		
		// get content
		self::$cache_content = preg_replace(array("#[\s]{1,+}#","#[\t]#"),"",ob_get_contents());
		
		// grava o arquivo de cache e so da permissão ao apache
		if(!file_exists(self::$cachefile))
		{
			$dir = dirname(self::$cachefile);
			if(!is_dir($dir))
				mkdir($dir, 0777, true);
			file_put_contents(self::$cachefile,"<!-- Phalanx Framewok  - [cacheDate:".date('Y-m-d H:i:s')."] -->\n\r".self::$cache_content,LOCK_EX);
			
		}
		
	}

	// end caching
	static function end()
	{
		
			self::cache_execute();
			ob_end_flush();
		
	}
}
