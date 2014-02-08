<?php
	
	class FacebookController extends Controller {
			
		private $post,
				$session,
				$get;
			
		protected $facebookAppID = FACEBOOK_APPID;
		protected $facebookAppSecret = FACEBOOK_SECRET;
		protected $facebookAppNamespace = FACEBOOK_APPNAMESPACE;
		protected $facebookAppToken = FACEBOOK_APPTOKEN; 
			
		public function init(){
			$this->post = Request::post();
			$this->get = Request::get();
			$this->session = new Session();
				
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			if($this->session->accept_token != REQUEST_TOKEN)
				Request::redirect(HOST.'login');
						
			Phalanx::loadClasses('Facebook', 'SocialNetwork');
		}
		
		public function login(){
			$facebook = new Facebook(array('appId' => FACEBOOK_APPID, 'secret' => FACEBOOK_SECRET, 'cookie' => true));
			$facebookLoginURL = $facebook->getLoginUrl(
				array(
					'scope' => 'publish_stream, publish_actions, email, user_about_me, user_likes, share_item, user_status',
					'redirect_uri' => HOST.'meu-perfil/redes-sociais/facebook/callback'
				)
			);
			
			Request::redirect($facebookLoginURL);
		}
		
		public function callback(){
			$facebook = new Facebook(array('appId' => FACEBOOK_APPID, 'secret' => FACEBOOK_SECRET, 'cookie' => true));
			
			#Recebo o token inicial, short-lived, de 2hrs
			$facebook = new Facebook(array('appId' => FACEBOOK_APPID, 'secret' => FACEBOOK_SECRET, 'cookie' => true));
			$facebook->setAccessToken($facebook->getAccessToken());
			
			#Trocamos o token short-lived por uma long lived
			$facebook->setExtendedAccessToken();
			$extendedAccessToken = $_SESSION["fb_".FACEBOOK_APPID."_access_token"];
			$facebook->setAccessToken($extendedAccessToken);
			$accessToken = $facebook->getAccessToken();
			
			# agora eu pego uns dados do cara
			$me = $facebook->api('/me');
			
			# e guardo o token c/ mais uns dados dele
			$o = new stdClass();
			$o->accessToken = $accessToken;
			$o->userID = $me['id'];
			$o->userName = $me['username'];
			
			SocialNetwork::link_account($this->session->user->id, SocialNetwork::FACEBOOK, $o);
			Request::redirect(HOST.'perfil/configuracoes');
		}
		
		public function logout(){
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			if($this->session->accept_token != REQUEST_TOKEN)
				Request::redirect(HOST.'login');
			
			SocialNetwork::unlink_account($this->session->user->id, SocialNetwork::FACEBOOK);
			Request::redirect(HOST.'perfil/configuracoes');
		}
		
		public function callOpenGraphAction($action, $params){
			$userToken = SocialNetwork::get_access_token($this->session->user->id, SocialNetwork::FACEBOOK);
						
			$f = new Facebook(array('appId' => FACEBOOK_APPID, 'secret' => FACEBOOK_SECRET, 'cookie' => true));
			$f->setAccessToken($this->facebookAppToken);
			$out = $f->api("/{$userToken->userID}/{$this->facebookAppNamespace}:{$action}", 'POST', $params);
			
			return $out;
		}
		
		public function sharePost($data=false){
			if(! $data)
				$data = $this->post;
							
			$facebook_token = SocialNetwork::get_access_token($data->uid, SocialNetwork::FACEBOOK);
			if($facebook_token){
				$facebook = new Facebook(array('appId' => FACEBOOK_APPID, 'secret' => FACEBOOK_SECRET, 'cookie' => true));
				$facebook->setAccessToken($facebook_token->accessToken);
				$facebook->api('/me/feed', 'post',
					array(
						'message' => $data->title,
						'link' => $data->url,
						'caption' => $data->content,
						'type' => 'link',
						'picture' => $data->avatar
					)
				);
			}
		}
	}