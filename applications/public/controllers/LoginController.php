<?php

	class LoginController extends Controller {
		
		private $cookies;
		private $session;
		private $post;
		private $get;
		
		public function init(){
			$this->post = Request::post();
			$this->get = Request::get();
			$this->session = new Session();
			$this->cookies = new Cookies();
		}
		
		public function Login(){
			if(isset($this->post->username) and isset($this->post->password)){
				Phalanx::loadClasses('Profile');
				$user = Profile::login($this->post->username, md5($this->post->password));
				
				$status = false;
				$reason = 'LoginError';
				
				if($user){
					$status = true;
					$next = $this->post->next;
					$reason = '';
					
					#Verifica se o usuário está banido
					if($user->banned == 1){
						$status = false;
						$reason = 'BannedLogin';
						
						# Se o usuário está banido e tenta forçar o login, adicionamos o IP dele na tabela de IPS rejeitados.
						$m = Model::Factory('rejected_ips');
						$m->where("ip='".REQUEST_IP."'");
						$m->delete();
						
						$m = Model::Factory('rejected_ips');
						$m->ip = REQUEST_IP;
						$m->reject_until = date("Y-m-d H:i:s", strtotime("+2 days"));
						$m->insert();
						
						$this->cookies->setExpire(strtotime("+2 days"));
						$this->cookies->isBanned = 1;
						
					}
					
					
					if($user->active == 0){
						$cancel_date = date_create($user->account_cancel_date);
						$today = date_create(date('Y-m-d'));
						$interval = date_diff($cancel_date, $today);
						if($interval < 30){
							$status = true;
							$next = 'perfil/configuracoes/reativar-conta';
						} else {
							$status = false;
							$reason = 'TimeLimitExceeded';
						}
					}

					$this->session->logged_in = true;
					$this->session->user = $user;
				#	$this->session->user->active = 1;
					$this->session->accept_token = md5(REQUEST_IP) . sha1(SESSION_SALT);
					
					
					 if($this->post->keep_me_logged_in == 1){
						$this->cookies->setExpire(strtotime("+15 days"));
						$this->cookies->logged_in = 1;
						$this->cookies->user = base64_encode($user->id);
						$this->cookies->expected_user = $user->login;
						$this->cookies->accepted_key = md5(md5($_SERVER['HTTP_USER_AGENT']) . $user->id . sha1(SESSION_SALT));
					}
				}
				
				
				if($status){
					Request::redirect(HOST . $next);
				}  else {
					$this->cookies->destroy('logged_in', 'user', 'accepted_key');
					$this->session->message = $reason;
					$this->session->accept_token = '';
					Request::redirect(HOST . 'login');
				}
			} else {
				$this->views = new Views(new Template("sign"));
				if($this->session->message != ''){
					$this->views->message = $this->session->message;
					$this->session->message = '';	
				}
				$this->views->next = (isset($this->get->next)) ? $this->get->next : ''; 
				$this->views->display("sign_in_form.phtml");
			}
		}
		
		public function Logout(){
			$this->session->destroy();
			$this->cookies->destroy('logged_in', 'user', 'accepted_key');
			
			$t = new Template("sign");
			$t->show_login_bar = true;
			$this->views = new Views($t);
			$this->views->display("logout.phtml");
		}
		
		public function checkStatus(){
			if($this->session->logged_in == true){
					if($this->session->user->active == 0){
						Request::redirect(HOST.'perfil/configuracoes/reativar-conta');
					}
				return true;
			} else if($this->cookies->logged_in == "1"){
				$status = $this->autoLogin();
				if($status) return true;
			}
			Request::redirect(HOST.'login');
		}
		
		protected function autoLogin(){
			if($this->cookies->logged_in=='' or $this->cookies->user=='')
				return false;
			
			if(! $this->cookies->expected_user or $this->cookies->expected_user=='')
				return false;
			
			if($this->cookies->logged_in == "1"){
				$uid = base64_decode($this->cookies->user);
				
				if(! $uid)
					return false;
				
				$expected_key = md5(md5($_SERVER['HTTP_USER_AGENT']) . $uid . sha1('SkyNerd a REDE SOCIAL do JoVemNerd'));
				if($expected_key == $this->cookies->accepted_key){
					$data = Model::Factory('user', false, false)->where("id='{$uid}'")->get();
					if(! $data) return false;
					
					if($data->active == 0)
						Request::redirect(HOST.'perfil/configuracoes/reativar-conta');
					
					if($this->cookies->expected_user != $data->login)
						return false;
					
					$this->session->user = $data;
					
					$m = Model::Factory('user_data');
					$m->where("user_id='{$uid}'");
					$this->session->user->other_data = $m->get();
					
					$this->session->logged_in = true;
					$this->session->accept_token = md5(REQUEST_IP) . sha1('SkyNerd a REDE SOCIAL do JoVemNerd');
					
					
					$m = Model::Factory('login_history');
					$m->user_id = $uid;
					$m->date = date('Y-m-d H:i:s');
					$m->ip = REQUEST_IP;
					$m->insert();
					
					$m = Model::Factory('user');
					$m->last_login = date('Y-m-d H:i:s');
					$m->where("id='{$uid}'");
					$m->update();
					
					return true;
				}
			}
			
			return false;
		}
		
		public function isLoggedIn(){
			$status = $this->session->logged_in && $this->session->user->active == 1;
			
			if($status){
				return true;
			} else{
				return $this->autoLogin();
			}
			
		}
		
	}
