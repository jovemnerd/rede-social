<?php
/*
//TODO Libera Cache
$last_modified_ts 	= floor(mktime()/30)*30;
$expires = ((60*60)*24)*15;

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_modified_ts) header('HTTP/1.1 304 Not Modified');
	
header("Pragma: public");
header('Cache-Control: must-revalidate');
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
header('Last-Modified: '.gmdate('d M Y H:i:s',$last_modified_ts).' GMT');
// ------------------------------------

//TODO Configura ETags
$content 	= floor(mktime()/30)*30;
$etag 		= md5($content);

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) header('HTTP/1.1 304 Not Modified');
	
header('Cache-Control: must-revalidate');
header('ETag: '.$etag);
// ------------------------------------
*/

error_reporting(E_ALL | E_STRICT);
ini_set('error_reporting', E_ALL ^E_NOTICE ^E_WARNING); 

require('settings.php');


	require('./Phalanx/Phalanx.php');
	require('urls.php');
	new Phalanx();	



