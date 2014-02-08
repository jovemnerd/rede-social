<?php

	class InstagramController extends Controller {
		private $config;
		private $session;
		
		public function init(){
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			$this->session = new Session;
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			Phalanx::loadClasses('Instagram');
			$this->config = array(
		        'client_id' => INSTAGRAM_CLIENT_ID,
		        'client_secret' => INSTAGRAM_CLIENT_SECRET,
		        'grant_type' => INSTAGRAM_GRANT_TYPE,
		        'redirect_uri' => HOST.INSTAGRAM_REDIRECT_URI
		     );
		}
		
		public function login(){
			$instagram = new Instagram($this->config);
			$instagram->openAuthorizationUrl();
		}
		
		public function logout(){
			$Session = new Session;
			Phalanx::loadClasses('SocialNetwork');
			SocialNetwork::unlink_account($Session->user->id, INSTAGRAM);	
			Request::redirect(HOST.'perfil/configuracoes');
		}
		
		public function callback(){
			Phalanx::loadClasses('SocialNetwork');
			
			$instagram = new Instagram($this->config);
			$instagram->setAccessCode(Request::get()->code);
			$access_token = $instagram->getOauthToken();
		
			$Session = new Session;
			SocialNetwork::link_account($Session->user->id, INSTAGRAM, $access_token);
			Request::redirect(HOST.'perfil/configuracoes');
		}
		
		public function feed(){
			Phalanx::loadClasses('SocialNetwork');
			$token = SocialNetwork::get_access_token(1, INSTAGRAM);
			$token = json_decode($token);
			
			$instagram = new Instagram($this->config);
			$instagram->setAccessToken($token->access_token);
			
			$popular = $instagram->getUserFeed();
			$response = json_decode($popular, true);
			
			foreach($response['data'] as $each){
				echo "<img src='{$each['images']['thumbnail']['url']}'>";
			}
		}
		
	}