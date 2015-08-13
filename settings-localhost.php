<?php
	
	define('PROJECT_DIR', 'rede-social');
	define('DEBUG', true);
	
	define('DATABASE_NAME', 'skynerd');
	define('DATABASE_USER', 'root');
	define('DATABASE_PASSWORD', '');
	define('DATABASE_HOST', '127.0.0.1');
 	
 	define('MASTER_DATABASE_NAME', 'skynerd');
	define('MASTER_DATABASE_USER', 'root');
	define('MASTER_DATABASE_PASSWORD', '');
	define('MASTER_DATABASE_HOST', '127.0.0.1');
	
	define('SLAVE_DATABASE_NAME', 'skynerd');
	define('SLAVE_DATABASE_USER', 'root');
	define('SLAVE_DATABASE_PASSWORD', '');
	define('SLAVE_DATABASE_HOST', '127.0.0.1');
	
	define('AVATAR_DIR', 'http://localhost/skynerd/media/images/avatar/');
	define('POST_IMAGES_DIR', 'http://'.$_SERVER['HTTP_HOST'].'/skynerd/media/images/posts/');	
	
	define('AVATAR_UPLOAD_DIR', '/Users/Guilherme/Sites/' . PROJECT_DIR . '/media/images/avatar/');
	define('POST_IMAGES_UPLOAD_DIR', '/Users/Guilherme/Sites/' . PROJECT_DIR . '/media/images/posts/');
	
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
	
	define('SESSION_SALT', 'aldfjhoduyf8osh4hbrwyftydfgdshfbhdsbfh');
	define('REQUEST_IP', $_SERVER['HTTP_X_FORWARDED_FOR']);
	define("REQUEST_TOKEN", md5($_SERVER['HTTP_X_FORWARDED_FOR']) . sha1(SESSION_SALT));
	define('REQUEST_PROTOCOL', 'http');
	
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
	define('USE_MEMCACHE', false);
	define('MEMCACHE_SECONDS', 30);
	

	#Configurações do pool de Memcache
	define('MEMCACHE_SERVER_1', 'localhost');
	define('MEMCACHE_PORT_1', 11211);

    define('MEMCACHE_SERVER_2', 'localhost');
    define('MEMCACHE_PORT_2', 11211);

    define('MEMCACHE_SERVER_3', 'localhost');
	define('MEMCACHE_PORT_3', 11211);
	
	mb_internal_encoding('UTF-8');
