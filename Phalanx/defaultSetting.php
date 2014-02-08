<?php

define('SEPD','/');
define('SEPU','/');

if(!defined('DEFAULT_CHARSET')) define('DEFAULT_CHARSET', 'uft-8'); 

if(!defined('ROOT')) define('ROOT',str_replace('/',SEPD,$_SERVER['DOCUMENT_ROOT']) . SEPD);

if(!defined('PROJECT_DIR')) define('PROJECT_DIR',null);

if(!defined('APPLICATION_DIR')) define('APPLICATION_DIR',ROOT.PROJECT_DIR.SEPD.'applications'.SEPD);

if(!defined('SYSTEM_DIR')) define('SYSTEM_DIR',ROOT.PROJECT_DIR.SEPD.'Phalanx'.SEPD);	

if(!defined('TEMPLATE_DIR')) define('TEMPLATE_DIR',ROOT.SEPD.PROJECT_DIR.SEPD.'templates'.SEPD);

if(!defined('TEMPLATE')) define('TEMPLATE','default');

if(!defined('TEMPLATE_FILE')) define('TEMPLATE_FILE','default.phtml');

if(!defined('VIEWS_DIR')) define('VIEWS_DIR','views'.SEPD);

if(!defined('CLASSES_DIR')) define('CLASSES_DIR','classes'.SEPD);

if(!defined('CONTROLLER_DIR')) define('CONTROLLER_DIR','controllers'.SEPD);

if(!defined('MODEL_DIR')) define('MODEL_DIR','models'.SEPD);

if(!defined('EXTENSIONS_DIR')) define('EXTENSIONS_DIR',ROOT.SEPD.PROJECT_DIR.SEPD.'extensions'.SEPD);

if(!defined('URI')) 
	if(PROJECT_DIR == '')	define('URI', str_replace(SEPU.PROJECT_DIR.SEPU,'',substr($_SERVER['REQUEST_URI'], 1)));
	else 					define('URI', str_replace(SEPU.PROJECT_DIR.SEPU,'',$_SERVER['REQUEST_URI']));

if(!defined('HOST'))
	if(PROJECT_DIR == '')	define('HOST',REQUEST_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].SEPU);
	else 					define('HOST',REQUEST_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].SEPU.PROJECT_DIR.SEPU);

if(!defined('TMP_DIR')) define('TMP_DIR',ROOT.PROJECT_DIR.SEPD.'tmp'.SEPD);

if(!defined('SESSION_PATH'))define('SESSION_PATH',TMP_DIR.'session'.SEPD);

if(!defined('CACHE_DIR')) define('CACHE_DIR',TMP_DIR.'cache'.SEPD);

if(!defined('LOG_DIR')) define('LOG_DIR',TMP_DIR.'logs'.SEPD);

if(!defined('MEDIA_DIR')) define('MEDIA_DIR', HOST.'media'.SEPD); # Url for media css|js|images|swf|flv|ico

if(!defined('STYLES_DIR')) define('STYLES_DIR',MEDIA_DIR.SEPD.'styles'.SEPD);

if(!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR',MEDIA_DIR.SEPD.'scripts'.SEPD);

if(!defined('IMAGES_DIR')) define('IMAGES_DIR',MEDIA_DIR.SEPD.'images'.SEPD);

if(!defined('LANGUAGES_PATH')) define('LANGUAGES_PATH',ROOT.PROJECT_DIR.SEPD.'languages'.SEPD);

if(!defined('UPLOAD_DIR')) define('UPLOAD_DIR', ROOT.PROJECT_DIR.SEPD.'media'.SEPD.'uploads'.SEPD); #Server folder to host the uploadedfiles

if(!defined('DEBUG')) define('DEBUG', 1);

if(!defined('LOG_FILE_FORMAT')) define('LOG_FILE_FORMAT', 'txt');

if(!defined('TIME_ZONE')) define('TIME_ZONE', 'America/New_York');

if(!defined('MEMCACHE_SERVER')) define('MEMCACHE_SERVER', 'localhost');
if(!defined('MEMCACHE_PORT')) define('MEMCACHE_PORT', 11211);
if(!defined('USE_MEMCACHE')) define('USE_MEMCACHE', true);
if(!defined('MEMCACHE_SECONDS')) define('MEMCACHE_SECONDS', 10);
			


ini_set('charset', DEFAULT_CHARSET);
error_reporting(E_ALL | E_STRICT);
ini_set('error_reporting', E_ALL ^E_NOTICE ^E_WARNING); 
ini_set('display_startup_errors', DEBUG);  
ini_set('display_errors', DEBUG); 
setlocale (LC_ALL, DEFAULT_LANGUAGE);
date_default_timezone_set(TIME_ZONE);