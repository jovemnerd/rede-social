<?php
	
	class YouTubeController extends Controller {
		
		private $post;
		
		public function init(){
			$this->post = Request::post();
			$this->session = new Session();
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			 
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
		} 
		
		public function callback(){
			Phalanx::loadClasses('Youtube');
			
			$query_str = parse_url(Request::get()->variables);
			parse_str($query_str['query'], $oAuthResponse);	
			$token = $oAuthResponse['token'];
			
			$tokens = new stdClass;
			$tokens->temporary = $token;
			$tokens->permanent = $this->exchangeToken($token);
			$youtube = new Youtube($tokens->permanent);
			$tokens->username = $youtube->username;
			
			Phalanx::loadClasses('SocialNetwork');
			SocialNetwork::link_account($this->session->user->id, YOUTUBE, $tokens);
			
			Request::redirect(HOST.'perfil/configuracoes');
		}
		
		public function login(){
			Request::redirect('https://www.google.com/accounts/AuthSubRequest?next=' . HOST.YOUTUBE_REDIRECT_URI . '&scope=http://gdata.youtube.com&session=1&secure=0');			
		}
		
		public function logout(){
			Phalanx::loadClasses('SocialNetwork');
			SocialNetwork::unlink_account($this->session->user->id, YOUTUBE);
			Request::redirect(HOST.'perfil/configuracoes');	
		}
		
		private function exchangeToken($single_use_token) {
		  $ch = curl_init("https://www.google.com/accounts/AuthSubSessionToken");
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  curl_setopt($ch, CURLOPT_FAILONERROR, true);
		  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Authorization: AuthSub token="' . $single_use_token . '"'
		  ));
		  
		  
		  if(USE_HTTP_PROXY == 1){
		  	curl_setopt($ch, CURLOPT_PROXY, HTTP_PROXY_HOST);
			curl_setopt($ch, CURLOPT_PROXYPORT, HTTP_PROXY_PORT);
		  }
		  
		  $result = curl_exec($ch);
		  curl_close($ch);
		  $splitStr = explode("=", $result);
		  
		  return trim($splitStr[1]);
		} 
		
	}
