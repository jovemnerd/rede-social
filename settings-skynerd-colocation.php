<?php

	define('PROJECT_DIR', '');
	define('DEBUG', 1);
 	
	define('MASTER_DATABASE_NAME', '');
	define('MASTER_DATABASE_USER', '');
	define('MASTER_DATABASE_PASSWORD', '');
	define('MASTER_DATABASE_HOST', '');
	
	define('SLAVE_DATABASE_NAME', '');
	define('SLAVE_DATABASE_USER', '');
	define('SLAVE_DATABASE_PASSWORD', '');
	define('SLAVE_DATABASE_HOST', '');
	
	define('AVATAR_UPLOAD_DIR', '');
	define('AVATAR_DIR', '');
	
	define('POST_IMAGES_UPLOAD_DIR', '');
	define('POST_IMAGES_DIR', '');
	
	# Configurações de Rede
	define('USE_HTTP_PROXY', false);
	define('HTTP_PROXY_HOST', '');
	define('HTTP_PROXY_PORT', '');
	
	#Redes sociais
	define('INSTAGRAM_CLIENT_ID', '');
	define('INSTAGRAM_CLIENT_SECRET', '');
	
	define('FACEBOOK_APPID', '');
	define('FACEBOOK_SECRET', '');
	define('FACEBOOK_APPTOKEN', '');
	define('FACEBOOK_APPNAMESPACE', '');
	
	define('REQUEST_IP', $_SERVER['HTTP_X_FORWARDED_FOR']);
	define("REQUEST_TOKEN", md5($_SERVER['HTTP_X_FORWARDED_FOR']) . sha1('SAAAAALT'));
	define('REQUEST_PROTOCOL', 'https');
	
	# Configurações de email
	define('MAIL_USERNAME', 'email@gmail.com');
	define('MAIL_PASSWORD', '');
	define('MAIL_HOST', 'ssl://smtp.gmail.com');
	define('MAIL_PORT', 465);
	define('MAIL_FROM', MAIL_USERNAME);
	define('MAIL_ALIAS', '');
	define('SMTP_SERVER_REQUIRE_AUTH', true);
	
	define('LOG_FILE_FORMAT', 'txt');
	define('LOG_DB_QUERIES', false);
	define('LOG_ONLY_DB_ERRORS', false);
	
	# CONFIGURAÇÕES DO MEMCACHE
	define('USE_MEMCACHE', true);
	define('MEMCACHE_SECONDS', 30);
	

	#Configurações do pool de Memcache
	define('MEMCACHE_SERVER_1', 'localhost');
	define('MEMCACHE_PORT_1', 11211);

    define('MEMCACHE_SERVER_2', 'localhost');
    define('MEMCACHE_PORT_2', 11211);

    define('MEMCACHE_SERVER_3', 'localhost');
	define('MEMCACHE_PORT_3', 11211);
	
	mb_internal_encoding('UTF-8');
