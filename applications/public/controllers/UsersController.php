<?php

	class UsersController extends Controller{
		
		private $session,
				$get,
				$post;
		
		public function init(){
			$this->session = new Session();
			
			$this->get = Request::get();
			$this->post = Request::post();
			
			Phalanx::loadClasses('Profile', 'Badges');
		}
		
		public function UserCard(){
			
			Phalanx::loadController("LoginController");
			
			$loginController = new LoginController();
			$status = $loginController->isLoggedIn();
			
			if($status){
				$v = new Views();
				$v->login = $this->session->user->login;
				$v->avatar = $this->session->user->other_data->avatar;
				$v->experience = Profile::experience($this->session->user->id);
				$v->badges = Badges::from_user($this->session->user->id, 4 );
				echo $v->render("user_mini_card.phtml");
			} else {
				$v = new Views();
				echo $v->render("user_mini_card_login.phtml");			
			}
			
		}
		
		public function Login(){
			$user = Profile::login($this->post->username, md5($this->post->password));
			$o = new stdClass;
			
			if($user and $user->banned != 1){
				$this->session->logged_in = true;
				$this->session->user = $user;
				$this->session->accept_token = md5(REQUEST_IP) . sha1('SkyNerd a REDE SOCIAL do JoVemNerd');
				
				$o->status = true;
				$o->login = $user->login;
				$o->avatar = $user->other_data->avatar;
				$o->experience = Profile::experience($this->session->user->id);
				$o->badges = Badges::from_user($this->session->user->id, 4 );	
			} else {
				$o->status = false;
				$o->reason = ($user->banned == 1) ? 'banned' : 'incorrect_info';
			}
			
			
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Methods: POST");
			header("Content-type: text/html; charset=utf-8");
			echo json_encode($o);
		}
		
		public function CheckAvailability(){
			if(! preg_match('/^[.A-Za-z0-9_-]+$/i', $this->post->new_username))
				die();
			
			$m = Model::Factory('user', false, false);
			$m->where("login='{$this->post->new_username}'");
			$user = $m->get();
			
			$o = new stdClass;
			$o->available = ($user) ? false : true;
			$o->username = $this->post->new_username;
			
			#Guarda as informações se o usuário já validou o usuário escolhido, p/ não possibilitar que ele pule este primeiro step
			$this->session->new_username = $this->post->new_username;
			$this->session->new_username_is_available = $o->available;
			
			header("Content-type: text/html; charset=utf-8");
			die(json_encode($o));
		}
		
		
		public function ChangeUsername(){
			if($this->session->new_username_is_available !== true)
				return;
			
			
			$m = Model::Factory('user', false, false);
			$m->login = $this->session->new_username;
			$m->where("id='{$this->session->user->id}'");
			$s = $m->update();
			
			if($s)
				$this->session->destroy();
			
			$o = new stdClass;
			$o->status = $s;
			
			
			
			header("Content-type: text/html; charset=utf-8");
			die(json_encode($o));
		}
		
	}
