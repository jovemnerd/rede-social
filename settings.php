<?php
	
	#Configurações do site	
	define('SITE_NAME', 'SkyNerd');
	define('TIME_ZONE' , 'America/Sao_Paulo');
	define('DATABASE_ENGINE', 'Mysql');
	define('NUMBER_OF_POSTS_LOADED_PER_TIME', 15);

	define('CACHE_VERSION', date('ymd'));
		
	switch($_SERVER['HTTP_HOST']){
		case 'localhost':
			require("settings-localhost.php");
			break;
			
		default:
			require("settings-skynerd-colocation.php");
			break;
	}
	
	
	
	define('TWITTER_CONSUMER_KEY', '');
	define('TWITTER_CONSUMER_SECRET', '');
	define('TWITTER_REDIRECT_URI', 'meu-perfil/redes-sociais/twitter/callback/');
	
	define('YOUTUBE_REDIRECT_URI', 'meu-perfil/redes-sociais/youtube/callback/');
	
	define('INSTAGRAM_GRANT_TYPE', 'authorization_code');
	define('INSTAGRAM_REDIRECT_URI', 'meu-perfil/redes-sociais/instagram/callback/');
