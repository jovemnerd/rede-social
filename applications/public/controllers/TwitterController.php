<?php
	
	class TwitterController extends Controller {
		
		private $post;
		private $get;
		private $session;
		
		public function init(){
			$this->session = new Session();
			$this->post = Request::post();
			$this->get = Request::get();
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
		}
		
		public function login(){
			Phalanx::loadClasses('twitteroauth');
			$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
			$request_token = $connection->getRequestToken(HOST.TWITTER_REDIRECT_URI);
			
			$this->session->oauth_token = $token = $request_token['oauth_token'];
			$this->session->oauth_token_secret = $request_token['oauth_token_secret'];
			 
			switch ($connection->http_code) {
				case 200:
					$url = $connection->getAuthorizeURL($token);
					header('Location: ' . $url); 
					break;
				
				default:
					echo 'Could not connect to Twitter. Refresh the page or try again later.';
					break;
			}
		}
		
		public function logout(){
			Phalanx::loadClasses('SocialNetwork');
			SocialNetwork::unlink_account($this->session->user->id, TWITTER);
			Request::redirect(HOST.'perfil/configuracoes');	
		}
		
		public function callback(){
			Phalanx::loadClasses('twitteroauth');
			$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $this->session->oauth_token, $this->session->oauth_token_secret);
			$access_token = $connection->getAccessToken($this->get->oauth_verifier);
			$this->session->access_token = $access_token;
			
			$this->session->destroy('oauth_token');
			$this->session->destroy('oauth_token_secret');
			
			if($connection->http_code == 200){
				$this->session->twitter_status = 'verified';
				
				Phalanx::loadClasses('SocialNetwork');
				SocialNetwork::link_account($this->session->user->id, TWITTER, $access_token);
				Request::redirect(HOST.'perfil/configuracoes/');
			}
		}
		
		public function sharePost($data=false){
			if(! $data)
				$data = $this->post;
			
			Phalanx::loadClasses("SocialNetwork");
			$twitter_token = SocialNetwork::get_access_token($data->uid, SocialNetwork::TWITTER);
			
			if($twitter_token){
				if(USE_HTTP_PROXY == 1){
					$context = stream_context_create(array('http' => array('proxy' => HTTP_PROXY_HOST.':'.HTTP_PROXY_PORT, 'request_fulluri' => true,)));
					$short_url = file_get_contents("http://migre.me/api.txt?url=" . $data->url, false, $context);	
				} else {
					$short_url = file_get_contents("http://migre.me/api.txt?url=" . $data->url);
				}
				
				Phalanx::loadClasses('twitteroauth', 'Twitter');
				$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $twitter_token['oauth_token'], $twitter_token['oauth_token_secret']);
				Twitter::post($connection, html_entity_decode($data->title) . ' ' . $short_url . ' #Skynerd');
			}
		}
		
	}